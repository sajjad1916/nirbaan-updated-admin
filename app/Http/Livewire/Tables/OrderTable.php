<?php

namespace App\Http\Livewire\Tables;

use App\Exports\OrdersExport;
use App\Models\Order;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;

use Illuminate\Support\Facades\Auth;

class OrderTable extends BaseDataTableComponent
{


    // public $header_view = 'components.buttons.new';
    public $per_page = 10;

    public $dataListQuery;
    public array $bulkActions = [
        'exportSelected' => 'Export',
    ];


    public function filters(): array
    {
        return [
            'status' => Filter::make(__("Status"))
                ->select([
                    '' => __('Any'),
                    'pending' => 'Pending',
                    'accepted' => 'Accepted',
                    'picked' => 'Picked',
                    'shipment' => 'In Shipment',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                    'failed' => 'Failed',
                ]),
            'payment_status' => Filter::make(__("Payment Status"))
                ->select([
                    '' => __('Any'),
                    'pending' => 'Pending',
                    'successful' => 'Successful',
                    'failed' => 'Failed',
                ]),
            'start_date' => Filter::make(__('Start Date'))
                ->date([
                    'min' => now()->subYear()->format('Y-m-d'), // Optional
                    'max' => now()->format('Y-m-d') // Optional
                ]),
            'end_date' => Filter::make(__('End Date'))
                ->date([
                    'min' => now()->subYear()->format('Y-m-d'), // Optional
                    'max' => now()->format('Y-m-d') // Optional
                ])
        ];
    }



    public function query()
    {

        $user = User::find(Auth::id());


        if ($user->hasRole('admin')) {
            $query = Order::fullData()->orderBy('id', "DESC");
        }else if($user->hasRole('client')){
         $query = Order::where('user_id',Auth::id())->orderBy('id', "DESC");
        } 
        
        else if ($user->hasRole('city-admin')) {
            $query = Order::with('vendor')->whereHas("vendor", function ($query) {
                return $query->where('creator_id', Auth::id());
            })->fullData()->orderBy('id', "DESC");
        } else {
            $query = Order::fullData()->where('vendor_id', Auth::user()->vendor_id)->orderBy('id', "DESC");
        }

        return $query->when($this->getFilter('status'), fn ($query, $status) => $query->currentStatus($status))
            ->when($this->getFilter('payment_status'), fn ($query, $pStatus) => $query->where('payment_status', $pStatus))
            ->when($this->getFilter('start_date'), fn ($query, $sDate) => $query->whereDate('created_at', ">=", $sDate))
            ->when($this->getFilter('end_date'), fn ($query, $eDate) => $query->whereDate('created_at', "<=", $eDate));
    }

    public function columns(): array
    {

        $columns = [
            Column::make(__('Actions'))->format(function ($value, $column, $row) {
                return view('components.buttons.order_actions', $data = [
                    "model" => $row
                ]);
            }),
            Column::make(__('ID'), 'id'),
            Column::make(__('Code'), 'code')->searchable()->sortable(),
            Column::make(__('User'), 'user.name')->searchable()->sortable(),
            Column::make(__('Status'), 'status')
                ->format(function ($value, $column, $row) {
                    return view('components.table.custom', $data = [
                        "value" => \Str::ucfirst($row->status)
                    ]);
                }),
            Column::make(__('Payment Status'), 'payment_status')
                ->format(function ($value, $column, $row) {
                    return view('components.table.custom', $data = [
                        "value" => \Str::ucfirst($row->payment_status)
                    ]);
                })->searchable()->sortable(),
            Column::make(__('Delivery Fee'), 'delivery_fee')->searchable()->sortable(),
            Column::make(__('Total'))->format(function ($value, $column, $row) {
                return view('components.table.order-total', $data = [
                    "model" => $row
                ]);
            })->searchable()->sortable(),
            Column::make('Method', 'payment_method.name')->searchable(),
        ];

        //
        if (Auth::user()->hasAnyRole('admin', 'city-admin')) {
            // array_push($columns, Column::make(__('Vendor'), 'vendor.name'));
        }

        array_push($columns, Column::make(__('Created At'), 'formatted_date'));
        return $columns;
    }


    public function exportSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            return Excel::download(new OrdersExport($this->selectedKeys), 'orders.xlsx');
        } else {
            //
        }
    }
}
