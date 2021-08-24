<style>
.warnings{
        background-color:#f5730d;
        width:100%;
        display:block;
        height:20px;
        color:white;
        margin-bottom:5px;
        text-align: center;
}

.success{
        background-color:chartreuse;
        color:black;
        display:block;
        height:20px;
        margin-bottom:10px;
        text-align:center;
}
</style>

<?php
// error_reporting(0);
require_once 'Classes/PHPExcel/IOFactory.php';
$url = "https://store-t0676dlrio.mybigcommerce.com/content/modifier/modifier.xlsx";
$dest = "./modifier.xlsx";
$raw = file_get_contents($url);
file_put_contents($dest, $raw);
$excelObject = PHPExcel_IOFactory::load('./modifier.xlsx');
$getSheet = $excelObject->getActiveSheet()->toArray(null);
$storehash = 't0676dlrio';
error_reporting(0);
$data = array();
array_splice($getSheet,0,1);
$data = $getSheet;
$prod_id = array();

//data to pass on BC
$result = array();


$option_values = array();
$label_splited = array();

//if have + then option was set to default
$default_flag = '+';



foreach($data as $key => $val){
      $product_id   = $val[0];
      $prod_id[] = $product_id;
      $type         = $val[+1];
      $required     = $val[+2];
      $display_name = $val[+3];
      $label        = $val[+4];
      $rules        = $val[+5];
      $value_data   = $val[+6]; //use for swatch option,picklist
      $config       = $val[+7]; //option (others) in BC dropdown option in modifiier
      $config_property = $val[+8];  //option (others) in BC dropdown option in modifiier
      
      $adjusters    = array();
      //check column two type in xlsx

         $label_arr = explode(';',$label);
         $rules_arr = explode(';',$rules);
         //extract label



         foreach($label_arr as $label_key => $label_val){   
                  //if string contains + then the option is set to default
                        if(strpos($label_val,$default_flag)){
                                //default option goes here
                                
                                $option_values[$label_key] = array(
                                        "label" => rtrim($label_val,$default_flag),
                                        "is_default" => TRUE,
                                        "value_data" => get_swatch_rules($value_data,rtrim($label_val,$default_flag))  == 'image_url' ? array('image_url'=> get_swatch_rules_image($value_data,rtrim($label_val,$default_flag))) : (get_swatch_rules($value_data,rtrim($label_val,$default_flag)) == 'product_id' ? array('product_id'=>get_swatch_rules_image($value_data,rtrim($label_val,$default_flag))) : get_swatch_rules($value_data,rtrim($label_val,$default_flag))),
                                        "adjusters" => adjusters(rtrim($label_val,$default_flag),$rules_arr) == null ? array() : adjusters(rtrim($label_val,$default_flag),$rules_arr)
                                );             
                        }else{
                                //non-default option
                                $option_values[$label_key] = array(
                                        "label" => $label_val,
                                        "is_default" => FALSE,
                                        "value_data" => get_swatch_rules($value_data,rtrim($label_val,$default_flag))  == 'image_url' ? array('image_url'=> get_swatch_rules_image($value_data,rtrim($label_val,$default_flag))) : (get_swatch_rules($value_data,rtrim($label_val,$default_flag)) == 'product_id' ? array('product_id'=>get_swatch_rules_image($value_data,rtrim($label_val,$default_flag))) : get_swatch_rules($value_data,rtrim($label_val,$default_flag))),
                                        "adjusters" => adjusters(rtrim($label_val,$default_flag),$rules_arr) == null ? array() : adjusters(rtrim($label_val,$default_flag),$rules_arr)
                                );
                        }
         }
         //get config
         if($config){
                 $a = explode(';',$config_property);
                 foreach($a as $k => $v){
                        $keyc   = substr($v,0,strpos($v,'='));
                        $valuec = substr($v,strpos($v,'=')+1); 

                        $data_config[] = array($keyc =>is_numeric($valuec) ? (int)$valuec : ($valuec == "true" ? true : ($valuec == "false" ? false : $valuec)));
                        if($keyc == "file_types_supported" || $keyc == "file_types_other"){  
                                $data_config[] = array($keyc => explode(',',$valuec));
                        }       
                 }



                 
         }
       
         //this result will be pass on BC json format
         $result[] = array(
                  'display_name' => $display_name,
                          'type' => $type,
                      'required' => $required,
                        'config' => array_filter(call_user_func_array("array_merge", $data_config))  == null ? array() :  array_filter(call_user_func_array("array_merge", $data_config)),
                 'option_values' => $type == "text" ? array() : ($type == "numbers_only_text" ? array() : ($type == "date" ? array() : ($type == "multi_line_text" ? array() : ($type == "checkbox" ? array() : ($type == "file" ? array() : $option_values)))))
         );
         //reset the option values to reuse when loop
       
         unset($data_config);
         unset($option_values);
         unset($d);
}

//functions to callback in loop
//get the string inside bracket
function getword_inside_bracket($haystack){
         $a = substr($haystack,strpos($haystack,'[')+1,strlen($haystack));
         $e = substr($a,0,strpos($a,']'));
         return $e;
} 
function get_rules($data){
        return substr($data,strpos($data,']')+1);
}
function get_swatch_rules($value_data,$val){
        $arr = explode(";",$value_data);
        $key = '';
        foreach($arr as $key => $value){ 
               $name = getword_inside_bracket($value);
                
               if($name == $val){
                       //get property name of value_data
                       $rule = substr($value,strpos($value,']')+1);
                       //check if image_url or colors  and set to max character to 9 only starting from 0 index
                       $rule_name  = substr($rule,0,10);
                       if(strpos($rule_name,'colors') !== false){  
                              $rule_filter = substr($value,strpos($value,'=')+1); 
                              $ex = explode(',',$rule_filter);
                              $key = array('colors'=>$ex);
                       }else if(strpos($rule_name,'image_url') !== false){
                              $key = 'image_url';
                       }else if(strpos($rule_name,'product_id') !== false){
                               $key = 'product_id';
                       }
                       return $key;

               }
        }
 
}





//get only image_url this is shit duplicate function 
function get_swatch_rules_image($value_data,$val){
        $arr = explode(";",$value_data);
        $key = '';
        foreach($arr as $key => $value){ 
               $name = getword_inside_bracket($value);
                
               if($name == $val){
                       //get property name of value_data
                       $rule = substr($value,strpos($value,']')+1);
                       //check if image_url or colors  and set to max character to 9 only starting from 0 index
                       $rule_name  = substr($rule,0,10);
                       if(strpos($rule_name,'image_url') !== false){
                               $rule_filter = substr($value,strpos($value,'=')+1); 
                               $key = $rule_filter;
                       }else if(strpos($rule_name,'product_id') !== false){
                                $rule_filter = substr($value,strpos($value,'=')+1); 
                                $key = $rule_filter;
                       }
                       return $key;

               }
        }
}

function adjusters($op_name,$rules_arr){
        foreach($rules_arr as $key => $val){
                $word = getword_inside_bracket($val);
                $splitted_word = explode(',',$word);
                foreach($splitted_word as $k => $v){
                        if($v === $op_name){
                                $rules_value =  get_rules($val);
                                $r = explode(',',$rules_value);
                                $dd = array();
                                foreach($r as $rk => $vr){
                                     $f = substr($vr,0,strpos($vr,'='));
                                    if($f == 'price'){
                                        $dd['price'] = array('adjuster' => 'relative','adjuster_value' => substr($vr,strpos($vr,'=')+1));
                                    }else if($f == 'weight'){
                                        $dd['weight'] = array('adjuster' => 'relative','adjuster_value' => substr($vr,strpos($vr,'=')+1));
                                    }
                                }
                                return $dd;     
                        }
                }
        }
}




// foreach($result as $temp => $r){
//     if($temp == 11){
//         echo json_encode($r,JSON_UNESCAPED_SLASHES);
//         die();
//     }    
// }

// echo json_encode($result);
// die();
foreach($result as $g => $v){

        $headers = array(
        'X-Auth-Client: iyhwjdyol0uamlpheeb1juma64pfkdj',
        'X-Auth-Token: em16zkoebf6o9ioq57h4kic88vprfr',
        'Content-Type: application/json'
        );
        $url = 'https://api.bigcommerce.com/stores/' .$storehash. '/v3/catalog/products/'.$prod_id[$g].'/modifiers';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($v,JSON_UNESCAPED_SLASHES));
        $response = curl_exec($ch);
        $data_result = json_decode($response,true);
        if($data_result['status'] == 422 || $data_result['status'] == "422"){
               echo '<div class="warnings">Modifer is already exist in excel row '. ($g + 1);
               echo ' Please double check and execute this script again';
               echo '</div>';
        }else if($data_result['data']){
                echo "<div class='success'>";
                echo 'Excel row ' . ($g + 1);
                echo ' was successfully uploaded in store';
                echo '</div>';
        }else{
                echo '<pre>';
                echo $response;
        }


}

?>