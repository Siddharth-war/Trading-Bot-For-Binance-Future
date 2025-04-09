<div class="table-responsive datatable-custom">
    <table id="datatable" class="width-100-percent">
        <tbody id="set-rows">
        <tr>
            <td>{{translate('#')}} </td>
            <td>{{translate('order')}}</td>
            <td>{{translate('date')}}</td>
            <td>{{translate('qty')}}</td>
            <td>{{translate('customer')}}</td>
            <td>{{translate('amount')}}</td>
        </tr>
        @foreach($data as $key=>$row)
            <tr>
                <td class="pull-right">
                    {{$key+1}}
                </td>
                <td class="table-column-pl-0">
                    <a href="{{route('admin.orders.details',['id'=>$row['order_id']])}}">{{$row['order_id']}}</a>
                </td>
                <td>{{date('d M Y',strtotime($row['date']))}}</td>
                <td>{{$row['quantity']}}</td>
                <td>
                    @if($row['customer'])
                        <a class="text-body text-capitalize">{{$row['customer']->f_name.' '.$row['customer']->l_name}}</a>
                    @else
                        <label
                            class="badge badge-danger">{{translate('invalid')}} {{translate('customer')}} {{translate('data')}}</label>
                    @endif
                </td>
                <td>{{ \App\CentralLogics\Helpers::set_symbol($row['price']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
