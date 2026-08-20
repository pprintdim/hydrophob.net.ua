<?php
class ModelExtensionShippingSShipping extends Model {
	
	public function procent($value, $tax_class_id, $procent = true) {		
		if ($tax_class_id && $procent) {			
			$amount = $this->getTax($value, $tax_class_id);			
			return $value + $amount;		
		} else {			
			return $value;		
		}	
	}

	function getQuote($address) {		
		$this->language->load('extension/shipping/s_shipping');				
		$s_shippings = $this->config->get('shipping_s_shipping');		
		
		$method_data = array();		
		$quote_data = array();		
		$sort_order = array();
		
		$cart_total = $this->cart->getTotal();

		$this->load->model('tool/image');

		foreach($s_shippings as $i => $delivery) {			
			if(!$delivery['status']) {				
				continue;			
			}

			if(!isset($delivery['cost'])) {				
				continue;			
			}

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$delivery['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");			if ($delivery['geo_zone_id'] == 0) {				$status = true;			} elseif ($query->num_rows) {				$status = true;			} else {				$status = false;			}
			
			if($status){				
				if ( isset($delivery['image']) and $delivery['image'] && file_exists(DIR_IMAGE .  $delivery['image'])) {					
					$thumb = $this->model_tool_image->resize($delivery['image'], 24, 24);				
				} else {					
					$thumb = '';				
				}				
				
				$s_shippings[$i]['thumb'] = $thumb;	

				if($delivery['type_cost'] == 2) {					
					if($delivery['free'] > 0){						
						if( $cart_total > $delivery['free']){							
							$cost= 0;						
						}else{							
							$cost = $cart_total*$delivery['cost']/100;						
						}					
					}else{						
						$cost = $cart_total*$delivery['cost']/100;					
					}		
				}elseif ($delivery['type_cost'] == 1){					
					if($delivery['free'] > 0){						
						if($cart_total > $delivery['free']){							
							$cost = 0;						
						}else{							
							$cost = $delivery['cost'];						
						}					
					}else{						
						$cost = $delivery['cost'];					
					}								
				}

				$text_free = $this->language->get('text_free');
				$description = $delivery['description'];					
				$img ='<img src="' . $thumb . '" align="middle">';					
				$name = '<b>' . $delivery['name'] . '</b>';

				$quote_data['s_shipping' . $i] = array(					
					'code' 			=> 	's_shipping.s_shipping' . $i,					
					'title' 		=> 	$name,					
					'description'	=>	$description,					
					'img'			=>	$thumb,					
					'image'			=>	$img,					
					'cost' 			=> 	$cost,					
					'tax_class_id' 	=>	$delivery['tax_class_id'],					
					'text' 			=> 	$cost > 0 ? $this->currency->format($this->tax->calculate($cost, $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) : '<b>' . $text_free . '</b>'						
				);

				$method_data = array(					
					'code'			=> 's_shipping',					
					'title'			=> $this->language->get('text_title'),					
					'quote'			=> $quote_data,					
					'sort_order' => $this->config->get('shipping_s_shipping_sort_order'),				
					'error' 		=> false				
				);			
			}			
		}		
		return $method_data;	
	}
}
?>