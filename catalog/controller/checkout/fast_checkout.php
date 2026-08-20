<?php
class ControllerCheckoutFastCheckout extends Controller {
    public function index() {
        $this->load->language('checkout/checkout');
        $this->load->model('checkout/order');
        $this->load->model('tool/image');

        $data['products'] = [];

        foreach ($this->cart->getProducts() as $product) {
            $data['products'][] = [
                'cart_id' => $product['cart_id'],
                'thumb'   => $this->model_tool_image->resize($product['image'], 50, 50),
                'name'    => $product['name'],
                'model'   => $product['model'],
                'quantity'=> $product['quantity'],
                'total'   => $this->currency->format($product['total'], $this->session->data['currency'])
            ];
        }

        $data['action'] = $this->url->link('checkout/fast_checkout/confirm', '', true);

        $this->response->setOutput($this->load->view('checkout/fast_checkout', $data));
    }

    public function confirm() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->cart->hasProducts()) {
                $this->response->redirect($this->url->link('checkout/cart'));
            }

            $this->load->model('checkout/order');

            // Данные гостя
            $guest = [
                'firstname' => $this->request->post['firstname'],
                'lastname'  => '',
                'email'     => $this->request->post['email'] ?? '',
                'telephone' => $this->request->post['telephone'],
                'payment_address' => [
                    'firstname' => $this->request->post['firstname'],
                    'address_1' => $this->request->post['address_1'],
                    'city'      => $this->request->post['city'],
                    'postcode'  => $this->request->post['postcode'],
                    'country_id'=> 0,
                    'zone_id'   => 0
                ],
                'shipping_address' => [
                    'firstname' => $this->request->post['firstname'],
                    'address_1' => $this->request->post['address_1'],
                    'city'      => $this->request->post['city'],
                    'postcode'  => $this->request->post['postcode'],
                    'country_id'=> 0,
                    'zone_id'   => 0
                ]
            ];

            $this->session->data['guest'] = $guest;

            // Методы оплаты и доставки
            $this->session->data['payment_method']  = ['code'=>$this->request->post['payment_method'],'title'=>'Оплата'];
            $this->session->data['shipping_method'] = ['code'=>$this->request->post['shipping_method'],'title'=>'Доставка'];

            // Создаём заказ
            $order_id = $this->model_checkout_order->addOrder($guest);

            // Ставим статус заказа
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));

            // Чистим корзину
            $this->cart->clear();

            // Редирект на success
            $this->response->redirect($this->url->link('checkout/success'));
        }
    }
}
