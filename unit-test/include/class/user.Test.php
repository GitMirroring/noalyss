<?php
use PHPUnit\Framework\TestCase;

define('USE_ID', 999999);
define('USE_FIRST_NAME', 'Unit test');
define('USE_NAME', 'UNIT');
define('USE_LOGIN', 'unit-test');
define('USE_ACTIVE', 1);
define('USE_PASS', 'passord');
define('USE_ADMIN', 0);
define('USE_EMAIL', 'none@dev.null.eu');

/**
 * @backupGlobals enabled
 * @coversDefaultClass \User
 */
require 'global.php';

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
    protected function setUp()
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

        $this->object=new User($this->cn, USE_ID);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        //drop user
        $this->cn->exec_sql('delete from jnt_use_dos where use_id=$1',
                array(USE_ID));
        $this->cn->exec_sql('delete from ac_users where use_id = $1',
                array(USE_ID));
    }

    /**
     * @covers User::load
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
     * @covers User::revoke_access
     * @todo   Implement testRevoke_access().
     */
    public function testRevoke_access()
    {
        $cn_dossier=new Database(DOSSIER);
        User::revoke_access(USE_LOGIN, DOSSIER);
        $this->assertEquals($cn_dossier->get_value('select count(*) from profile_user where user_name =$1 ',
                        array(USE_LOGIN)), 0);
    }

    /**
     * @covers User::grant_admin_access
     * @todo   Implement testGrant_admin_access()
     */
    public function testGrant_admin_access()
    {
        $cn_dossier=new Database(DOSSIER);
        User::grant_admin_access(USE_LOGIN, DOSSIER);

        $this->assertEquals($cn_dossier->get_value('select count(*) from profile_user where user_name =$1 ',
                        array(USE_LOGIN)), 1);
    }
    /**
     * @covers User::remove_inexistant_user
     */
    public function testRemove_inexistant_user() {
        // insert inexisting user
        $cn=new Database (DOSSIER);
        $cn->exec_sql('insert into profile_user (user_name,p_id) values ($1,$2)',
                array('unknown/user',1));
        
        //verify presence of it in DOSSIER
        $this->assertEquals($cn->get_value('select count(*) from profile_user where user_name=$1',array('unknown/user')),1);
        
        //remove him
        User::remove_inexistant_user(DOSSIER);
        
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
        $this->object=new User(Dossier::connect(), USE_ID);
        
        $restore=$this->object->get_periode();
        $this->assertTrue(is_numeric($restore),"Old periode id is not an integer");
        $this->object->set_periode($p_id);
        $this->assertEquals($p_id,$this->object->get_periode(),"Cannot retrieve the right periode");
    }
}
