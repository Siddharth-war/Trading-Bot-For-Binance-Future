<link rel="stylesheet" href="https://cdn.datatables.net/1.11.1/css/jquery.dataTables.min.css">

<table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 mt-3" id="datatable">
    <thead class="thead-light">
        <tr>
            <th>{{translate('SL')}} </th>
            <th>{{translate('order')}}</th>
            <th>{{translate('date')}}</th>
            <th>{{translate('qty')}}</th>
            <th>{{translate('amount')}}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data as $key=>$row)
        <tr>
            <td class="">
                {{$key+1}}
            </td>
            <td class="">
                <a href="{{route('admin.orders.details',['id'=>$row['order_id']])}}">{{$row['order_id']}}</a>
            </td>
            <td>{{date('d M Y',strtotime($row['date']))}}</td>
            <td>{{$row['quantity']}}</td>
            <td>{{ \App\CentralLogics\Helpers::set_symbol($row['price']) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function () {
        $('input').addClass('form-control');
    });
    var datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
        dom: 'Bfrtip',
        "iDisplayLength": 25,
    });
</script>
