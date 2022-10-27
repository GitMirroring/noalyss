<?php
use PHPUnit\Framework\TestCase;

define('USE_ID', 999999);
define('USE_FIRST_NAME', 'Unit test');
define('USE_NAME', 'UNIT');
define('USE_LOGIN', 'unit-test');
define('USE_ACTIVE', 1);
define('USE_PASS', md5('password'));
define('USE_ADMIN', 0);
define('USE_EMAIL', 'none@dev.null.eu');

/**
 * @backupGlobals enabled
 * @coversDefaultClass \Noalyss_User
 */
require DIRTEST.'/global.php';

class UserTest extends TestCase
{

    /**
     * @var User
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        // create database connx : 
        $this->cn=new Database();
        // create a user 
        $this->cn->exec_sql('delete from jnt_use_dos where use_id=$1',
                array(USE_ID));
        $this->cn->exec_sql('delete from ac_users where use_id=$1',
                array(USE_ID));
        $this->cn->exec_sql('insert into ac_users (use_id,use_first_name,use_name,use_login,use_active,use_pass,use_admin,use_email) values ($1,$2,$3,$4,$5,$6,$7,$8)',
                array(USE_ID, USE_FIRST_NAME, USE_NAME, USE_LOGIN, USE_ACTIVE, USE_PASS,
            USE_ADMIN, USE_EMAIL));

        $this->object=new Noalyss_user($this->cn, USE_ID);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        //drop user
        $this->cn->exec_sql('delete from jnt_use_dos where use_id=$1',
                array(USE_ID));
        $this->cn->exec_sql('delete from ac_users where use_id = $1',
                array(USE_ID));
    }

    /**
     * @covers Noalyss_user::load
     */
    public function testLoad()
    {

        $this->object->load();
        $this->assertEquals($this->object->id, USE_ID);
        $this->assertEquals($this->object->name, USE_NAME);
        $this->assertEquals($this->object->first_name, USE_FIRST_NAME);
        $this->assertEquals($this->object->login, USE_LOGIN);
        $this->assertEquals($this->object->active, USE_ACTIVE);
        $this->assertEquals($this->object->password, USE_PASS);
        $this->assertEquals($this->object->admin, USE_ADMIN);
        $this->assertEquals($this->object->email, USE_EMAIL);
    }

    /**
     * @covers Noalyss_user::revoke_access
     * @todo   Implement testRevoke_access().
     */
    public function testRevoke_access()
    {
        $cn_dossier=new Database(DOSSIER);
        Noalyss_user::revoke_access(USE_LOGIN, DOSSIER);
        $this->assertEquals($cn_dossier->get_value('select count(*) from profile_user where user_name =$1 ',
                        array(USE_LOGIN)), 0);
    }

    /**
     * @covers Noalyss_user::grant_admin_access
     * @todo   Implement testGrant_admin_access()
     */
    public function testGrant_admin_access()
    {
        $cn_dossier=new Database(DOSSIER);
        Noalyss_user::grant_admin_access(USE_LOGIN, DOSSIER);

        $this->assertEquals($cn_dossier->get_value('select count(*) from profile_user where user_name =$1 ',
                        array(USE_LOGIN)), 1);
    }
    /**
     * @covers Noalyss_user::remove_inexistant_user
     */
    public function testRemove_inexistant_user() {
        // insert inexisting user
        $cn=new Database (DOSSIER);
        $cn->exec_sql('insert into profile_user (user_name,p_id) values ($1,$2)',
                array('unknown/user',1));
        
        //verify presence of it in DOSSIER
        $this->assertEquals($cn->get_value('select count(*) from profile_user where user_name=$1',array('unknown/user')),1);
        
        //remove him
        Noalyss_user::remove_inexistant_user(DOSSIER);
        
        // check his removal
        $this->assertEquals($cn->get_value('select count(*) from profile_user where user_name=$1',array('unknown/user')),0);
    }
    public function dataPeriode()
    {
        return array(
            [92],
            [103],
            [105],
            [107],
            [108],
            [137],
            [1137]
        );
    }
    /**
     * 
     * @param type $p_id periode id
     * @dataProvider dataPeriode
     */
    public function testPeriode($p_id)
    {
        $this->object=new Noalyss_user(Dossier::connect(), USE_ID);
        
        $restore=$this->object->get_periode();
        $this->assertTrue(is_numeric($restore),"Old periode id is not an integer");
        $this->object->set_periode($p_id);
        $this->assertEquals($p_id,$this->object->get_periode(),"Cannot retrieve the right periode");
    }
    /**
     * @brief test the save_password function
     */
    public function testSave_Password() 
    {
        // password is in MD5 
        $old_password=$this->object->getPassword();
        $this->assertEquals(USE_PASS,$old_password,"Password mismatch");
        
        $this->assertFalse($this->object->save_password("test1","test2"),"Passwords must be identical");
        $this->assertTrue($this->object->save_password("test2","test2"),"Identical passwords seen as different");
        $this->object->load();
        $new_password = $this->object->getPassword();
        $this->assertTrue(($old_password != $new_password),"Password not changed old=$old_password new=$new_password");
        $this->assertTrue($new_password=='ad0234829205b9033196ba818f7a872b',"Password incorrect");
        
        
        
    }
    /**
     * @brief test the writable profile : W (Read Write) and O (Read Write NO delete)
     */
    public function testsql_writable_profile()
    {
        $cn=Dossier::connect();
        $user=new Noalyss_user($cn);
         $_SESSION[SESSION_KEY.'use_admin']=0;
         $user->admin=0;
        $this->assertEquals(0 , $user->getAdmin()," Error user is admin");
        
        $sql=$user->sql_writable_profile();
        $sql= " select count(*) from ".$sql." as a";

        $this->assertEquals(3,$cn->get_value($sql),"Error writable profile must be = 3");
        $this->assertEquals(3,count($user->get_writable_profile()),"Error writable profile must be = 3");
        
        $sql=$user->sql_readable_profile();
        $sql= " select count(*) from ".$sql." as a";
        
        $this->assertEquals(3,$cn->get_value($sql),"Error readable profile must be = 3");
        $this->assertEquals(3,count($user->get_writable_profile()),"Error readable profile must be = 3");
        
        // remove profile 1
        $cn->exec_sql("delete from user_sec_action_profile where p_id=$1 and p_granted=$2",[$user->get_profile(),1]);
            
        $sql=$user->sql_writable_profile();
        $sql= " select count(*) from ".$sql." as a";

        
        $this->assertEquals(2,$cn->get_value($sql),"Error writable profile must be = 2 ");
        $this->assertEquals(2,count($user->get_writable_profile()),"Error writable profile must be = 2");
        
        $sql=$user->sql_readable_profile();
        $sql= " select count(*) from ".$sql." as a";
        
        $this->assertEquals(2,$cn->get_value($sql),"Error readable profile must be = 2");
        $this->assertEquals(2,count($user->get_writable_profile()),"Error readable profile must be = 2");
        
        // add profile 1 read only
         $cn->exec_sql("insert into user_sec_action_profile(p_id,p_granted,ua_right) values($1,$2,$3)"
                 ,[$user->get_profile(),1,"R"]);
        
        $sql=$user->sql_writable_profile();
        $sql= " select count(*) from ".$sql." as a";
        
        
        $this->assertEquals(2,$cn->get_value($sql),"Error writable profile must be = ");
        $this->assertEquals(2,count($user->get_writable_profile()),"Error writable profile must be = 2");
        
        $sql=$user->sql_readable_profile();
        $sql= " select count(*) from ".$sql." as a";

        $this->assertEquals(3,$cn->get_value($sql),"Error readable profile must be = 3");
        $this->assertEquals(3,count($user->get_readable_profile()),"Error readable profile must be = 3");
        
        // update  profile 1 O Write and no suppress
         $cn->exec_sql("update user_sec_action_profile set ua_right = $3 where p_id =$1 and p_granted = $2"
                 ,[$user->get_profile(),1,"O"]);
        
        $sql=$user->sql_writable_profile();
        $sql= " select count(*) from ".$sql." as a";
        
        $this->assertEquals(3,$cn->get_value($sql),"Error writable profile must be = ");
        $this->assertEquals(3,count($user->get_writable_profile()),"Error writable profile must be = 2");
        
        $sql=$user->sql_readable_profile();
        $sql= " select count(*) from ".$sql." as a";
        
        $this->assertEquals(3,$cn->get_value($sql),"Error readable profile must be = 3");
        $this->assertEquals(3,count($user->get_writable_profile()),"Error readable profile must be = 3");
         
        // update  profile 1 W Write 
         $cn->exec_sql("update user_sec_action_profile set ua_right = $3 where p_id =$1 and p_granted = $2"
                 ,[$user->get_profile(),1,"W"]);
          $_SESSION[SESSION_KEY.'use_admin']=1;
            $user->admin=1;
    }
}
