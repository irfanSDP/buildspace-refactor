<?php

use PCK\Orders\OrderRepository;

class OrderController extends \BaseController {

    private $orderRepository;

	public function __construct(
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
	}

	public function index()
	{
        return \View::make('orders.index');
	}

    public function getList()
    {
        $data = [];
        $user = \Confide::user();

        if ($user->isSuperAdmin()) {
            $orders = $this->orderRepository->getOrders();
        } else {
            if ($user->hasCompany()) {
                $orders = $this->orderRepository->getOrdersByCompanyId($user->company_id);
            } else {
                return \Response::json($data);
            }
        }

        foreach($orders as $order)
        {
            // Each order is tied to a buyer (company)
            // Each sub order is tied to a seller (company)

            // Get sub order
            $orderSubs = $order->orderSubs;
            $orderSub = $orderSubs->first();

            // Get order item
            $orderItems = $orderSub->orderItems;
            $orderItem = $orderItems->first();

            // Get payment
            $orderPayment = $order->orderPayment;

            // Get buyer
            $buyer = ($order->company) ? $order->company->name : '';

            // Get project (if any)
            $project = $orderItem->project;

            // Get seller
            if ($project) {
                $subsidiary = $project->subsidiary;
                $rootSubsidiary = $subsidiary->getTopParentSubsidiary('root');
                $seller = $rootSubsidiary->company->name;
            } else {
                $seller = ($orderSub->company) ? $orderSub->company->name : 'N/A';
            }

            // Get order date
            $orderDate = \DateTime::createFromFormat('Y-m-d H:i:s', $order->created_at);

            // Prepare row
            $row = [
                'id' => $order->id,
                'date' => $orderDate->format('d-M-Y g:i A'),
                'referenceId' => $order->reference_id,
                'type' => $this->orderRepository->getTypeLabel($orderItem->type),
                'project' => $project ? $project->title : 'N/A',
                'projectReference' => $project ? $project->reference : 'N/A',
                'buyer' => $buyer,
                'seller' => $seller,
                'total' => $orderPayment->total,
                'status' => $this->orderRepository->getStatusLabel($orderPayment->status),
            ];

            $data[] = $row;
        }

        return \Response::json($data);
    }
}