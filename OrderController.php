<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderModel;
use App\Models\ItemModel;
use App\Models\TableNumber;
use App\Models\BranchModel;
use App\Models\ItemOption;
use App\Models\LogModel;
use App\Models\ChargesModel;

use App\Events\OrderEvent;
use Auth;

 
class OrderController extends MainController
{
   

    public function save(Request $request)
    {

        try
        {
            $charges                    = ChargesModel::first();
            $item_id                    = $request->item_id;
            $order_quantity             = $request->order_quantity;
            $instruction                = $request->instruction;
            $branch                     = BranchModel::first();
            $order                      = new OrderModel;
            $order->table_id            = $request->table_id;
            $order->branch_id           = isset($branch->branch_id) ? $branch->branch_id : 0;
            $order->tablet_mac_address  = $request->ip();
            $order->voucher_code        = $request->voucher_code;
            $order->order_mode          = $request->order_mode;
            $order->tax_rate            = $charges->sales_tax;
            $order->service_rate        = $charges->service_charge;
            $order->save();
            OrderBrk::where('order_id',$order->order_id)->delete();

            foreach($item_id as $key => $item)
            {
                $item_id                            = explode('_', $item);
                $option_id                          = isset($item_id[1]) ? $item_id[1] : 0;
                $items                              = ItemModel::where('item_id', $item_id[0])->first();
                $order_item                         = new OrderItemModel;
                $order_item->order_id               = $order->order_id;
                $order_item->item_id                = $item_id[0];
                $order_item->option_id              = $option_id;
                $order_item->order_quantity         = $order_quantity[$key];
                $order_item->order_price            = $items->item_price;
                $order_item->special_instruction    =  $instruction[$key];
                $order_item->save();
                Self::order_brk($order->order_id, $items, $order_quantity[$key], $option_id, $instruction[$key]);
            }


            $data['order_id'] = $order->order_id;
            $data['table_id'] = $request->table_id;
            // $data['table_name'] = 
            event(New OrderEvent(Self::order_details($order->order_id), 'NewOrder'));
            $table                  = TableNumber::where('table_id', $request->table_id)->first();
            $logs                   = new LogModel;
            $logs->user_id          = Auth::user()->id;
            $logs->logs_type        = 'Order Created';
            $logs->logs_description = 'Created a new order for table '.$table->table_number.' Order ID : '.$order->order_id;
            $logs->save();
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    	 
    }

    public function order_details($order_id)
    {
        $order = OrderModel::single($order_id)->first();
        $data['order_id'] = $order_id;
        $data['table_id'] = 0;
        $data['table_name'] = '';
        if(!is_null($order))
        {
            $data['table_id'] = $order->table_id;
            $data['table_name'] = $order->table_number;
        }

        return $data;
    }

    
}
