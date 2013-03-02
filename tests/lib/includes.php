<?php

$path = str_replace('\\', '/', dirname(__DIR__ )) .'/../';
$lib_path = $path . 'lib/';
require_once('PHPUnit/Autoload.php');
require_once($path . 'BinaryBeast.php');
require_once($lib_path . 'BBSimpleModel.php');
require_once($lib_path . 'BBModel.php');
require_once($lib_path . 'BBHelper.php');
require_once($lib_path . 'BBCache.php');
require_once($lib_path . 'BBCountry.php');
require_once($lib_path . 'BBGame.php');
require_once($lib_path . 'BBLegacy.php');
require_once($lib_path . 'BBMap.php');
require_once($lib_path . 'BBMatch.php');
require_once($lib_path . 'BBMatchGame.php');
require_once($lib_path . 'BBRace.php');
require_once($lib_path . 'BBRound.php');
require_once($lib_path . 'BBTeam.php');
require_once($lib_path . 'BBTournament.php');

$bb = new BinaryBeast('e17d31bfcbedd1c39bcb018c5f0d0fbf.4dcb36f5cc0d74.24632846');
$bb->disable_ssl_verification();

require_once 'PHPUnit/Framework/Assert.php';
class bb_test_case extends PHPUnit_Framework_TestCase {
    
    /** @var BinaryBeast */
    protected $bb;
    
    function __construct($name = NULL, array $data = array(), $dataName = '') {
        global $bb;
        $this->bb = &$bb;

        parent::__construct($name, $data, $dataName);
    }
    
    public function history() {
        return array($this->object->result_history, $this->object->error_history);
    }
    
    /**
     * Check against a returned list to verify that each object in the array has the 
     *  specified attributes
     * 
     * Each value in $keys can either be a string, to simply check for the existance of a property, 
     * or it can be a key => value pair with any of the following values, to check the property type:
     *      'numeric', 'int', 'float', 'string', 'array', 'object', 'boolen', 'null'
     * 
     * @param array $list
     * @param array $keys
     * @param string $msg
     */
    public static function assertListFormat($list, $keys = array(), $msg = 'Returned objects does not have expected values') {
        self::assertTrue(is_array($list), 'provided value is not an array');
        self::assertTrue(sizeof($list) > 0, 'provided value was empty, unable to verify contents');

        //run assertObjectFormat on every item returned in the array
        foreach($list as $object) self::assertObjectFormat($object, $keys);
    }
    /**
     * Validate an object format
     * 
     * 
     * Each value in $keys can either be a string, to simply check for the existance of a property, 
     * or it can be a key => value pair with any of the following values, to check the property type:
     *      'numeric', 'int', 'float', 'string', 'array', 'object', 'boolen', 'null'
     * 
     * @param object $object
     * @param array $keys
     */
    public static function assertObjectFormat($object, $keys = array()) {
        foreach($keys as $key => $type) {
            //Validate existance only
            if(is_numeric($key)) {
                $key = $type;
                $type = null;
                
            }

            //Assert property exists
            self::assertObjectHasAttribute($key, $object, 'Object does not have attribute ' . $key);

            //Assert the property type
            if(!is_null($type)) {
                $method = 'is_' . $type;
                $valid = $method($object->$key) || is_null($object->$key);
                self::assertTrue($valid, $key . ' is not a valid ' . $type . ' value!');
            }
        }
    }
    /**
     * Checks a list to see if any of its elements has the provided key=>value pair[s]
     * @param array $list
     * @param mixed $key
     * @param mixed $value
     */
    public static function assertListContains($list, $key, $value) {
        $valid = false;
        foreach($list as $object) {
            if($object->$key == $value) {
                $valid = true;
                break;
            }
        }
        self::assertTrue($valid, 'Provided list did not have any objects with ' . $key . ' equal to ' . $value);
    }

    public static function assertServiceIsObject($svc, $msg = 'Service result is not an object!') {
        return self::assertTrue(is_object($svc), $msg);
    }

    /**
     * Verify that a service result simply contains 'result' => 200
     */
    public static function assertServiceSuccessful($result, $msg = 'Service did not return property result = 200!') {
        self::assertServiceIsObject($result);
        self::assertAttributeEquals(200, 'result', $result, $msg);
    }
    /**
     * Assert that a service result successfully returned a list of objects
     * 
     * @param object $result
     * @param string $list_name defaults to 'list', property name of the list returned
     * @param string $msg
     */
    public static function assertServiceListSuccessful($result, $list_name = 'list', $msg = 'Service did not successfully return a list') {
        self::assertServiceSuccessful($result);
        self::assertObjectHasAttribute($list_name, $result, $msg);
        self::assertTrue(is_array($result->$list_name), "Result $list_name value is not an array");
        if(sizeof($result->$list_name) > 0) {
            self::assertTrue(is_object($result->{$list_name}[0]), "Values in $list_name are not objects!");
        }
    }
    /**
     * Assert that the result code of a service call indicated an authorization error
     */
    public static function assertServicePermissionDenied($result, $msg = 'Result code was NOT 403!') {
        self::assertServiceSuccessful($result);
        self::assertAttributeEquals(403, 'result', $result, $msg);
    }
    /**
     * Test a svc result to see if it was loaded from local cache
     */
    public static function assertServiceLoadedFromCache($result) {
        self::assertServiceSuccessful($result);
        self::assertAttributeEquals(true, 'from_cache', $result);
    }
    /**
     * Test a svc result to see if it was NOT loaded from local cache
     */
    public static function assertServiceNotLoadedFromCache($result) {
        self::assertServiceSuccessful($result);
        self::assertTrue(!isset($result->from_cache));
    }
}

?>  