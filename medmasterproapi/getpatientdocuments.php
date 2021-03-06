<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require 'classes.php';

$xml_string = "";
$xml_string = "<patientdocuments>";

$token = $_POST['token'];
$patient_id = $_POST['patientId'];
$category_id = isset($_POST['categoryId']) ? $_POST['categoryId'] : '';


//$token = 'fe15082d987f3fd5960a712c54494a68';
//$patient_id = 1;
//$category_id = 5;

if ($userId = validateToken($token)) {
    $strQuery = "SELECT d.id,d.date,d.size,d.url,d.docdate,d.mimetype,c2d.category_id
                                FROM `documents` AS d
                                INNER JOIN `categories_to_documents` AS c2d ON d.id = c2d.document_id
                                WHERE foreign_id = {$patient_id}";

    if ($category_id) {
        $strQuery .= " AND category_id = {$category_id}";
    }
    $strQuery .= " ORDER BY category_id, d.date DESC";

    $result = $db->get_results($strQuery);


//    $result = $db->get_results($strQuery);


    if ($result) {
        $xml_string .= "<status>0</status>";
        $xml_string .= "<reason>The Contact Record has been fetched</reason>";

        for ($i = 0; $i < count($result); $i++) {
            $xml_string .= "<document>\n";

            foreach ($result[$i] as $fieldName => $fieldValue) {
                if ($fieldName == 'url') {
                    if (!empty($fieldValue)) {
                        $fieldValue = getUrl($fieldValue);
                    }else{
                        $fieldValue = '';
                    }
                }
                $rowValue = xmlsafestring($fieldValue);
                $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
            }
            $xml_string .= "</document>\n";
        }
    } else {
        $xml_string .= "<status>-1</status>";
        $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</patientdocuments>";
echo $xml_string;
?>