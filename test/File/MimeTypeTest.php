<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File;

use Laminas\Validator;
use Laminas\Validator\File;

/**
 * MimeType testbed
 *
 * @category   Laminas
 * @package    Laminas_Validator_File
 * @subpackage UnitTests
 * @group      Laminas_Validator
 */
class MimeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $valuesExpected = array(
            array(array('image/jpg', 'image/jpeg'), true),
            array('image', true),
            array('test/notype', false),
            array('image/gif, image/jpg, image/jpeg', true),
            array(array('image/vasa', 'image/jpg', 'image/jpeg'), true),
            array(array('image/jpg', 'image/jpeg', 'gif'), true),
            array(array('image/gif', 'gif'), false),
            array('image/jp', false),
            array('image/jpg2000', false),
            array('image/jpeg2000', false),
        );

        $filetest = __DIR__ . '/_files/picture.jpg';
        $files = array(
            'name'     => 'picture.jpg',
            'type'     => 'image/jpg',
            'size'     => 200,
            'tmp_name' => $filetest,
            'error'    => 0
        );

        foreach ($valuesExpected as $element) {
            $options   = array_shift($element);
            $expected  = array_shift($element);
            $validator = new File\MimeType($options);
            $validator->enableHeaderCheck();
            $this->assertEquals(
                $expected,
                $validator->isValid($filetest, $files),
                "Test expected " . var_export($expected, 1) . " with " . var_export($options, 1)
                . "\nMessages: " . var_export($validator->getMessages(), 1)
            );
        }
    }

    /**
     * Ensures that getMimeType() returns expected value
     *
     * @return void
     */
    public function testGetMimeType()
    {
        $validator = new File\MimeType('image/gif');
        $this->assertEquals('image/gif', $validator->getMimeType());

        $validator = new File\MimeType(array('image/gif', 'video', 'text/test'));
        $this->assertEquals('image/gif,video,text/test', $validator->getMimeType());

        $validator = new File\MimeType(array('image/gif', 'video', 'text/test'));
        $this->assertEquals(array('image/gif', 'video', 'text/test'), $validator->getMimeType(true));
    }

    /**
     * Ensures that setMimeType() returns expected value
     *
     * @return void
     */
    public function testSetMimeType()
    {
        $validator = new File\MimeType('image/gif');
        $validator->setMimeType('image/jpeg');
        $this->assertEquals('image/jpeg', $validator->getMimeType());
        $this->assertEquals(array('image/jpeg'), $validator->getMimeType(true));

        $validator->setMimeType('image/gif, text/test');
        $this->assertEquals('image/gif,text/test', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text/test'), $validator->getMimeType(true));

        $validator->setMimeType(array('video/mpeg', 'gif'));
        $this->assertEquals('video/mpeg,gif', $validator->getMimeType());
        $this->assertEquals(array('video/mpeg', 'gif'), $validator->getMimeType(true));
    }

    /**
     * Ensures that addMimeType() returns expected value
     *
     * @return void
     */
    public function testAddMimeType()
    {
        $validator = new File\MimeType('image/gif');
        $validator->addMimeType('text');
        $this->assertEquals('image/gif,text', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text'), $validator->getMimeType(true));

        $validator->addMimeType('jpg, to');
        $this->assertEquals('image/gif,text,jpg,to', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text', 'jpg', 'to'), $validator->getMimeType(true));

        $validator->addMimeType(array('zip', 'ti'));
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text', 'jpg', 'to', 'zip', 'ti'), $validator->getMimeType(true));

        $validator->addMimeType('');
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text', 'jpg', 'to', 'zip', 'ti'), $validator->getMimeType(true));
    }

    public function testSetAndGetMagicFile()
    {
        if (!extension_loaded('fileinfo')) {
            $this->markTestSkipped('This PHP Version has no finfo installed');
        }

        $validator = new File\MimeType('image/gif');
        $magic     = getenv('magic');
        if (!empty($magic)) {
            $mimetype  = $validator->getMagicFile();
            $this->assertEquals($magic, $mimetype);
        }

        $this->setExpectedException('Laminas\Validator\Exception\InvalidArgumentException', 'could not be');
        $validator->setMagicFile('/unknown/magic/file');
    }

    public function testSetMagicFileWithinConstructor()
    {
        if (!extension_loaded('fileinfo')) {
            $this->markTestSkipped('This PHP Version has no finfo installed');
        }

        $this->setExpectedException('Laminas\Validator\Exception\InvalidMagicMimeFileException', 'could not be used by ext/finfo');
        $validator = new File\MimeType(array('image/gif', 'magicFile' => __FILE__));
    }

    public function testOptionsAtConstructor()
    {
        $validator = new File\MimeType(array(
            'image/gif',
            'image/jpg',
            'enableHeaderCheck' => true));

        $this->assertTrue($validator->getHeaderCheck());
        $this->assertEquals('image/gif,image/jpg', $validator->getMimeType());
    }

    /**
     * @group Laminas-9686
     */
    public function testDualValidation()
    {
        $valuesExpected = array(
            array('image', true),
        );

        $filetest = __DIR__ . '/_files/picture.jpg';
        $files = array(
            'name'     => 'picture.jpg',
            'type'     => 'image/jpg',
            'size'     => 200,
            'tmp_name' => $filetest,
            'error'    => 0
        );

        foreach ($valuesExpected as $element) {
            $options   = array_shift($element);
            $expected  = array_shift($element);
            $validator = new File\MimeType($options);
            $validator->enableHeaderCheck();
            $this->assertEquals(
                $expected,
                $validator->isValid($filetest, $files),
                "Test expected " . var_export($expected, 1) . " with " . var_export($options, 1)
                . "\nMessages: " . var_export($validator->getMessages(), 1)
            );

            $validator = new File\MimeType($options);
            $validator->enableHeaderCheck();
            $this->assertEquals(
                $expected,
                $validator->isValid($filetest, $files),
                "Test expected " . var_export($expected, 1) . " with " . var_export($options, 1)
                . "\nMessages: " . var_export($validator->getMessages(), 1)
            );
        }
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258()
    {
        $validator = new File\MimeType(array(
            'image/gif',
            'image/jpg',
            'headerCheck' => true));
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertTrue(array_key_exists('fileMimeTypeNotReadable', $validator->getMessages()));
        $this->assertContains("'nofile.mo'", current($validator->getMessages()));
    }

    public function testDisableMagicFile()
    {
        $validator = new File\MimeType('image/gif');
        $magic     = getenv('magic');
        if (!empty($magic)) {
            $mimetype  = $validator->getMagicFile();
            $this->assertEquals($magic, $mimetype);
        }

        $validator->disableMagicFile(true);
        $this->assertTrue($validator->isMagicFileDisabled());

        if (!empty($magic)) {
            $mimetype  = $validator->getMagicFile();
            $this->assertEquals($magic, $mimetype);
        }
    }

    /**
     * @group Laminas-10461
     */
    public function testDisablingMagicFileByConstructor()
    {
        $files = array(
            'name'     => 'picture.jpg',
            'size'     => 200,
            'tmp_name' => dirname(__FILE__) . '/_files/picture.jpg',
            'error'    => 0,
            'magicFile' => false,
        );

        $validator = new File\MimeType($files);
        $this->assertFalse($validator->getMagicFile());
    }
}
