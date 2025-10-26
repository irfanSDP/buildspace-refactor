<?php namespace PCK\Orders;

use PCK\Helpers\Key;

class OrderRepository
{
    public function getOrderById($id)
    {
        return Order::find($id);
    }

    public function getOrderByReferenceId($referenceId)
    {
        return Order::where('reference_id', $referenceId)->first();
    }

    public function getOrders()
    {
        return Order::orderBy('id', 'DESC')->get();
    }

    public function getOrdersByCompanyId($companyId)
    {
        return Order::where('company_id', $companyId)
            ->orWhereHas('orderSubs', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function getOrderByProjectTender($userId, $projectId, $tenderId)
    {
        return Order::where('user_id', $userId)
            ->whereHas('orderSubs.orderItems.orderItemProjectTender', function($query) use ($projectId, $tenderId) {
                $query->where('project_id', $projectId)
                    ->where('tender_id', $tenderId);
            })
            ->whereHas('orderPayment', function($query) {
                $query->where('status', OrderPayment::STATUS_SUCCESS);
            })
            ->first();
    }

    public function getTypeLabel($type)
    {
        return OrderItem::getTypeLabel($type);
    }

    public function getStatusLabel($status)
    {
        return OrderPayment::getTypeLabel($status);
    }

    public function generateReferenceId($type)
    {
        $now = new \DateTime();
        $prefix = $now->format('ymd');
        $length = 6;
        $column = 'reference_id';

        switch ($type) {
            case 'master_order':
                $table = 'orders';
                break;
            case 'sub_order':
                $table = 'order_subs';
                break;
            case 'invoice':
                $table = 'order_payments';
                break;
            default:
                $table = 'orders';
        }

        while( Key::keyInTable($table, $string = $prefix . strtoupper(Key::generateRandomString($length)), $column) )
        {
            //loop until unique string is generated
        }

        return $string;
    }

    public function create($data)
    {
        // Create the order
        $order = new Order();
        $order->reference_id = $this->generateReferenceId('master_order');
        $order->user_id = $data['buyerId'];
        $order->company_id = $data['buyerCompanyId'];
        $order->origin = $data['origin'];
        $order->save();

        $orderId = $order->id;  // Retrieve the ID of the saved order

        // Create the sub order
        $subOrder = new OrderSub();
        $subOrder->order_id = $orderId;
        $subOrder->reference_id = $this->generateReferenceId('sub_order');
        $subOrder->company_id = ! empty($data['sellerCompanyId']) ? $data['sellerCompanyId'] : null;
        $subOrder->total = $data['price'];
        $subOrder->save();

        $subOrderId = $subOrder->id;  // Retrieve the ID of the saved sub order

        // Create the order item
        $orderItem = new OrderItem();
        $orderItem->order_sub_id = $subOrderId;
        $orderItem->type = $data['type'];
        $orderItem->quantity = 1;
        $orderItem->total = $data['price'];
        $orderItem->save();

        if (! empty($data['projectId']) && ! empty($data['tenderId'])) {
            // Create the order item project tender
            $orderItemProjectTender = new OrderItemProjectTender();
            $orderItemProjectTender->order_item_id = $orderItem->id;
            $orderItemProjectTender->project_id = $data['projectId'];
            $orderItemProjectTender->tender_id = $data['tenderId'];
            $orderItemProjectTender->save();
        }

        // Create the order payment
        $orderPayment = new OrderPayment();
        $orderPayment->order_id = $orderId;
        $orderPayment->payment_gateway = $data['paymentGateway'];
        $orderPayment->reference_id = $this->generateReferenceId('invoice');
        $orderPayment->total = $data['price'];
        $orderPayment->status = OrderPayment::STATUS_PENDING;
        $orderPayment->description = $data['description'];
        $orderPayment->save();

        return $order;
    }

    public function updatePayment($result)
    {
        $order = $this->getOrderByReferenceId($result->reference_id);
        if (! $order) {
            return false;
        }
        $orderPayment = $order->orderPayment;
        if (! $orderPayment) {
            return false;
        }
        if (empty($orderPayment->transaction_id)) {
            $orderPayment->transaction_id = $result->transaction_id;
        }
        $orderPayment->status = $result->status;
        $orderPayment->save();

        return true;
    }
}