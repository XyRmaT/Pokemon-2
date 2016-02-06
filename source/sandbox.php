<?php
$date = date("Y-m-d");
function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
    }
    if(is_array($array)) {
        foreach($array as $key => $value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

try {
    $client = new SoapClient('http://192.168.10.202:1180/HisWebService.asmx?wsdl');

    $xml = "<?xml version='1.0' encoding='gb2312' ?>
	<DocumentElement><DataTable>
	<AccessKey>N1k4gf65xH2evvFBj/gJ86L5dC8EYNbR</AccessKey>
	<MethodName>paiban_t_employee</MethodName>
	<date_dat>2015-12-18</date_dat>
	<deptcode_vchr>3040000</deptcode_vchr>
	</DataTable></DocumentElement>";


    //$return =$client->__sosacall('GetHisWebService',$xml);

    $return = $client->GetHisWebService(array('Xml' => $xml));

    $array = object_array($return);


    $string = implode(" ", $array);


    $xstr = simplexml_load_string(str_replace("gb2312", "UTF-8", $string));

    $arr = (json_decode(json_encode($xstr), TRUE));

    if(empty($arr)) {
        echo "请检查你的工号！$number
		是否存在!";
    } else {
        $arry = ($arr['DataTable']);

        //print_r($arr['DataTable']);


        // var_dump($arry);
        //$array=object_array($return);
    }

} catch(SOAPFault $e) {

    print_r('Exception:' . $e->getMessage());

}

?>