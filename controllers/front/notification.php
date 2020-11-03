<?php

class DibsEasyNotificationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        sleep(5);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $easy_body = file_get_contents('php://input');
            $easy_body = json_decode($easy_body, JSON_PRETTY_PRINT);

            if ($easy_body !== FALSE) {
                if (isset($easy_body['id'])) {
                    $easy_information = $easy_body['data'];
                    if (isset($easy_information['paymentId'])) {
                        $paymentId = $easy_information['paymentId'];
                        $reference = $easy_information['order']['reference'];

                        $id_cart = explode('D', $reference);
                        $id_cart = $id_cart[0];

                        if (isset($id_cart) AND (int)$id_cart > 0) {
                            $cart = new Cart($id_cart);
                            if ($cart->OrderExists()) {
                                // The order has already been created
                            } else {
                                $order = $this->module->placeOrder($id_cart);
                            }
                        }
                    }
                }
            }
        }
    }
}