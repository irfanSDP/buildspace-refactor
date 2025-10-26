<?php

namespace Api;

use Illuminate\Support\Facades\Crypt;
use PCK\Orders\OrderRepository;
use Request;
use Response;

class OrderController extends \BaseController
{
    private $orderRepository;

    public function __construct(
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    // This method is expecting to receive order data which has been encrypted (laravel) and therefore will not work when receiving from external systems
    public function store()
    {
        $result = ['success' => false];

        $data = Request::all();
        if (isset($data['oid'])) {
            $referenceId = $data['oid'];
        }
        if (isset($data['od'])) {
            $orderData = unserialize(Crypt::decrypt($data['od']));
        }
        if (! isset($referenceId) || ! isset($orderData)) {
            return Response::json($result);
        }

        $order = $this->orderRepository->create($orderData);
        if (! $order) {
            return Response::json($result);
        }
        $result['success'] = true;

        return Response::json($result);
    }
}
