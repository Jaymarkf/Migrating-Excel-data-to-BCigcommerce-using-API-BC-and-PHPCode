<?php
// error_reporting(0);
require_once 'Classes/PHPExcel/IOFactory.php';
$url = "https://store-t0676dlrio.mybigcommerce.com/content/modifier/update_modifier.xlsx";
$dest = "./update_modifier.xlsx";
$raw = file_get_contents($url);
file_put_contents($dest, $raw);
$excelObject = PHPExcel_IOFactory::load('./update_modifier.xlsx');
$getSheet = $excelObject->getActiveSheet()->toArray(null);
$storehash = 't0676dlrio';
error_reporting(0);
$data = array();
array_splice($getSheet,0,1);
$data = $getSheet;
$prod_id = array();
$mod_id = array();
//data to pass on BC
$result = array();


$option_values = array();
$label_splited = array();

//if have + then option was set to default
$default_flag = '+';



foreach($data as $key => $val){
      $product_id   = $val[0];
      $prod_id[] = $product_id;
      $mod_id[] = $val[+1];
      $type         = $val[+2];
      $required     = $val[+3];
      $display_name = $val[+4];
      $label        = $val[+5];
      $rules        = $val[+6];
      $value_data   = $val[+7]; //use for swatch option,picklist
      $config       = $val[+8]; //option (others) in BC dropdown option in modifiier
      $config_property = $val[+9];  //option (others) in BC dropdown option in modifiier
      $option_id = $val[+10];
      
      
      $adjusters    = array();
      //check column two type in xlsx

         $label_arr = explode(';',$label);
         $rules_arr = explode(';',$rules);
         //extract label
       $option_idd = explode(',',$option_id);


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
                                if($option_idd[$label_key] == "" || $option_idd[$label_key] == false || $option_idd[$label_key] == null){
                                        unset($option_values[$label_key]["id"]);                             
                                }else{
                                        $option_values[$label_key]["id"] = (int)$option_idd[$label_key];
                                }               
                        }else{
                                //non-default option
                                           
                             
                                $option_values[$label_key] = array(                       
                                        "label" => $label_val,
                                        "is_default" => FALSE,
                                        "value_data" => get_swatch_rules($value_data,rtrim($label_val,$default_flag))  == 'image_url' ? array('image_url'=> get_swatch_rules_image($value_data,rtrim($label_val,$default_flag))) : (get_swatch_rules($value_data,rtrim($label_val,$default_flag)) == 'product_id' ? array('product_id'=>get_swatch_rules_image($value_data,rtrim($label_val,$default_flag))) : get_swatch_rules($value_data,rtrim($label_val,$default_flag))),
                                        "adjusters" => adjusters(rtrim($label_val,$default_flag),$rules_arr) == null ? array() : adjusters(rtrim($label_val,$default_flag),$rules_arr)
                                );
                                if($option_idd[$label_key] == "" || $option_idd[$label_key] == false || $option_idd[$label_key] == null){
                                        unset($option_values[$label_key]["id"]);                             
                                }else{
                                        $option_values[$label_key]["id"] = (int)$option_idd[$label_key];
                                }       

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


// echo json_encode($result);
// die();
// echo '<pre>';
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

/*
 
Copyright (c) 2010, dealnews.com, Inc.
All rights reserved.
 
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
 
 * Redistributions of source code must retain the above copyright notice,
   this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
 * Neither the name of dealnews.com, Inc. nor the names of its contributors
   may be used to endorse or promote products derived from this software
   without specific prior written permission.
 
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
 
 */
 
/**
 * show a status bar in the console
 * 
 * <code>
 * for($x=1;$x<=100;$x++){
 * 
 *     show_status($x, 100);
 * 
 *     usleep(100000);
 *                           
 * }
 * </code>
 *
 * @param   int     $done   how many items are completed
 * @param   int     $total  how many items are to be done total
 * @param   int     $size   optional size of the status bar
 * @return  void
 *
 */
 
function show_status($done, $total, $size=30) {
 
    static $start_time;
 
    // if we go over our bound, just ignore it
    if($done > $total) return;
 
    if(empty($start_time)) $start_time=time();
    $now = time();
 
    $perc=(double)($done/$total);
 
    $bar=floor($perc*$size);
 
    $status_bar="\r[";
    $status_bar.=str_repeat("=", $bar);
    if($bar<$size){
        $status_bar.=">";
        $status_bar.=str_repeat(" ", $size-$bar);
    } else {
        $status_bar.="=";
    }
 
    $disp=number_format($perc*100, 0);
 
    $status_bar.="] $disp%  $done/$total";
 
    $rate = ($now-$start_time)/$done;
    $left = $total - $done;
    $eta = round($rate * $left, 2);
 
    $elapsed = $now - $start_time;
 
    $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";
 
    echo "$status_bar  ";

    flush();
 
    // when done, send a newline
    if($done == $total) {

    }
 
}
 


// foreach($result as $temp => $r){
//     if($temp == 11){
//         echo json_encode($r,JSON_UNESCAPED_SLASHES);
//         die();
//     }    
// }


foreach($result as $g => $v){
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.bigcommerce.com/stores/t0676dlrio/v3/catalog/products/'.$prod_id[$g].'/modifiers'. '/'. $mod_id[$g],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
  CURLOPT_POSTFIELDS =>json_encode($v),
  CURLOPT_HTTPHEADER => array(
    'X-Auth-Client: iyhwjdyol0uamlpheeb1juma64pfkdj',
    'X-Auth-Token: em16zkoebf6o9ioq57h4kic88vprfr',
    'Content-Type: application/json'
  ),
));
$response = curl_exec($curl);
curl_close($curl);

get_status(json_decode($response,true),$g);

}


function get_status($code,$row){
        if($code['status'] == 422 || $code['status'] == "422"){
                echo "\n\e[1;31;40m".$code['title']. " Excel row -> " .($row+1)." \e[0m \r\n";
        }else if($code['data']){
                echo "\n\e[1;36;40m"."Excel row:".($row+1)." was successfully updated in store \e[0m \r\n";
        }else{ 
                echo print_r($code);    
        }
}
?>