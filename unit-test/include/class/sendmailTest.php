<?php

/*
 * * Copyright (C) 2022 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * 
 * Author : Dany De Bontridder danydb@noalyss.eu $(DATE)
 */

/**
 * @file
 * @brief noalyss
 */

use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

define ("ALLOWED_EMAIL_DOMAIN","linux.org,localhost");

/**
 * @testdox Class SendmailTest : used for ...
 * @backupGlobals enabled
 * @coversDefaultClass Sendmail
 */
class SendmailTest extends TestCase
{

    /**
     * @testdox verify that email has the mandatory filed
     * @covers       Sendmail::verify
     * @backupGlobals enabled
     */
    function testVerifyCorrect()
    {
        $sendmail=new \Sendmail();
        $sendmail->mailto("dany@gmail.com");
        $sendmail->set_from("web@localhost");
        $sendmail->set_subject("Test envoi");
        $sendmail->set_message("corps du message");
        try {
            $sendmail->verify();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false," check email fails");
        }

    }
    /**
     * @testdox verify that email fails it mailto is absent
     * @covers       Sendmail::verify
     * @backupGlobals enabled
     */
    function testVerifyFailsMailto()
    {
        $sendmail=new \Sendmail();
        $sendmail->mailto("");
        $sendmail->set_from("web@localhost");
        $sendmail->set_subject("Test envoi");
        $sendmail->set_message("corps du message");
        $this->expectExceptionCode(EXC_INVALID);
        $sendmail->verify();

    }
    /**
     * @testdox verify that email fails it recipient is absent
     * @covers       Sendmail::verify
     * @backupGlobals enabled
     */
    function testVerifyFailsFrom()
    {
        $sendmail=new \Sendmail();
        $sendmail->mailto("web@localhost");
        $sendmail->set_from("");
        $sendmail->set_subject("Test envoi");
        $sendmail->set_message("corps du message");
        $this->expectExceptionCode(EXC_INVALID);
        $sendmail->verify();

    }
    /**
     * @testdox verify that email fails it subject is absent
     * @covers       Sendmail::verify
     * @backupGlobals enabled
     */
    function testVerifyFailsSubject()
    {
        $sendmail=new \Sendmail();
        $sendmail->mailto("web@localhost");
        $sendmail->set_from("dany@gmail.com");
        $sendmail->set_message("corps du message");
        $this->expectExceptionCode(EXC_INVALID);
        $sendmail->verify();

    }
    /**
     * @testdox verify that email fails it content is absent
     * @covers       Sendmail::verify
     * @backupGlobals enabled
     */
    function testVerifyFailsContent()
    {
        $sendmail=new \Sendmail();
        $sendmail->mailto("");
        $sendmail->set_from("web@localhost");
        $sendmail->set_subject("Test envoi");
        $this->expectExceptionCode(EXC_INVALID);
        $sendmail->verify();

    }
    /**
     * @testdox Check that the from of email respect Domain : domain allowed
     * @covers       Sendmail::verify
     * @backupGlobals enabled
     */
    function testVerifyDomainFails()
    {
        $sendmail=new \Sendmail();
        $sendmail->mailto("web@localhost");
        $sendmail->set_from("dany@gmail.com");
        $sendmail->set_subject("Test envoi");
        $sendmail->set_message("corps du message");

        $this->expectExceptionCode(EXC_INVALID);
        $sendmail->verify();

    }
    /**
     * @testdox Check that the from of email respect Domain : domain forbidden
     * @covers       Sendmail::verify
     * @backupGlobals enabled
     */
    function testVerifyDomainSuccess()
    {
        $sendmail=new \Sendmail();
        $sendmail->mailto("web@localhost");
        $sendmail->set_from("dany@linux.org");
        $sendmail->set_subject("Test envoi");
        $sendmail->set_message("corps du message");

        $sendmail->verify();
        $this->assertTrue(true);

    }
    /*
     *@testdox test increment email
     *@covers Sendmail::increment_mail Sendmail::get_email_sent
     */
    function testIncrement_mail()
    {
        $cn=new \Database();
        $dos_id=DOSSIER;
        $sendmail=new Sendmail();
        $cn->exec_sql("delete from dossier_sent_email where dos_id=$dos_id and de_date='20100101'");
        $email_sent=$sendmail->get_email_sent($cn, $dos_id, '20100101');
        $this->assertTrue($email_sent == 0,"error cannot count email get $email_sent");

        $sendmail->increment_mail($cn, $dos_id, "20100101");
        $email_sent=$sendmail->get_email_sent($cn, $dos_id, '20100101');
        $this->assertTrue($email_sent == 1,"error cannot count email get $email_sent");

        $sendmail->increment_mail($cn, $dos_id, "20100101");
        $email_sent=$sendmail->get_email_sent($cn, $dos_id, '20100101');
        $this->assertTrue($email_sent == 2,"error cannot count email");

        $sendmail->increment_mail($cn, $dos_id, "20100101");
        $email_sent=$sendmail->get_email_sent($cn, $dos_id, '20100101');
        $this->assertTrue($email_sent == 3,"error cannot count email $email_sent");

        $cn->exec_sql("delete from dossier_sent_email where dos_id=$dos_id and de_date='20100101'");

    }
}