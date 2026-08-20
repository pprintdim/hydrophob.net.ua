<?php
    class ControllerExtensionShippingSShipping extends Controller {
        private $error = array();
        
        public function index() {   				
            $this->load->language('extension/shipping/s_shipping');	

            $this->document->setTitle($this->language->get('heading_title'));

            $this->load->model('setting/setting');		
           
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {			
                $this->model_setting_setting->editSetting('shipping_s_shipping', $this->request->post);					
                $this->session->data['success'] = $this->language->get('text_success');			
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));		
            }
            
            $data['tab_module'] = $this->language->get('tab_module');		
            
            if (isset($this->error['warning'])) {			
                $data['error_warning'] = $this->error['warning'];		
            } else {			
                $data['error_warning'] = '';		
            }  		
            
            $data['breadcrumbs'] = array();   		
            $data['breadcrumbs'][] = array(       		
                'text'      => $this->language->get('text_home'),			
                'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)   		
            );   		
            
            $data['breadcrumbs'][] = array(       		
                'text'      => $this->language->get('text_shipping'),			
                'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)   		
            );   		
            
            $data['breadcrumbs'][] = array(       		
                'text'      => $this->language->get('heading_title'),			
                'href'      => $this->url->link('extension/shipping/s_shipping', 'user_token=' . $this->session->data['user_token'], true)   		
            );		
            
            $data['action'] = $this->url->link('extension/shipping/s_shipping', 'user_token=' . $this->session->data['user_token'], true);		
            $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);		
            
            $data['modules'] = array();		
            
            if (isset($this->request->post['shipping_s_shipping'])) {			
                $data['modules'] = $this->request->post['shipping_s_shipping'];		
            } elseif ($this->config->get('shipping_s_shipping')) {			
                $data['modules'] = $this->config->get('shipping_s_shipping');		
            }		
            
            if (isset($this->request->post['shipping_s_shipping_status'])) {			
                $data['shipping_s_shipping_status'] = $this->request->post['shipping_s_shipping_status'];		
            } elseif ($this->config->get('shipping_s_shipping_status')) {			
                $data['shipping_s_shipping_status'] = $this->config->get('shipping_s_shipping_status');		
            }else{			
                $data['shipping_s_shipping_status']=1;		
            }

		    if (isset($this->request->post['shipping_s_shipping_sort_order'])) {
			$data['shipping_s_shipping_sort_order'] = $this->request->post['shipping_s_shipping_sort_order'];
		    } else {
			$data['shipping_s_shipping_sort_order'] = !$this->config->get('shipping_s_shipping_sort_order') ? 1 : $this->config->get('shipping_s_shipping_sort_order');
		    }
            
            $data['user_token'] = $this->session->data['user_token'];		

            $this->load->model('tool/image');	

            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);		
            $data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);		
            
            foreach ($data['modules'] as $key => $module) {            
                if ( isset($module['image']) and $module['image'] && file_exists(DIR_IMAGE .  $module['image'])) {                
                    $thumb = $this->model_tool_image->resize($module['image'], 100, 100);            
                }else {                
                    $thumb = $this->model_tool_image->resize('no_image.png', 100, 100);            
                }           
                
                $data['modules'][$key]['thumb'] = $thumb;       
            
            }		
            
            $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);		
            
            $this->load->model('localisation/tax_class');		
            $this->load->model('localisation/geo_zone');	

            $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();		
            $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();		
            
            $this->load->model('design/layout');		
            
            $data['layouts'] = $this->model_design_layout->getLayouts();		
            
            $this->load->model('localisation/language');		
            
            $data['languages'] = $this->model_localisation_language->getLanguages();		
            
            $data['header'] = $this->load->controller('common/header');		
            $data['column_left'] = $this->load->controller('common/column_left');		
            $data['footer'] = $this->load->controller('common/footer');		
            
            $this->response->setOutput($this->load->view('extension/shipping/s_shipping', $data));	
        }	
        
        protected function validate() {		
            if (!$this->user->hasPermission('modify', 'extension/shipping/s_shipping')) {			
                $this->error['warning'] = $this->language->get('error_permission');		
            }		
            
            foreach ($this->request->post['shipping_s_shipping'] as  $value) {			
                if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 32)) {				
                    $this->error['name'] = '';			
                }		
            }		
            
            if ($this->error && !isset($this->error['warning'])) {			
                $this->error['warning'] = $this->language->get('error_warning');		
            }		
            
            return !$this->error;	
        }
    }
?>