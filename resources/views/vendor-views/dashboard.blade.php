@extends('layouts.vendor.app')

@section('title', translate('Invoice'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" src="{{ asset('public/assets/admin') }}/vendor/apex/apexcharts.css">
    </link>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between items-center">
            <div class=" bg-white">

                <ul class="nav nav-pills mb-3 border" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">

                        <a href="{{ route('vendor.invoice') }}" class="nav-link {{ request()->has("param") ? '' : 'active' }}">All invoices</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('vendor.invoice',["param"=>'due']) }}" class="nav-link {{ request()->get("param") == 'due' ? 'active' : '' }}">Due date invoices</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('vendor.invoice',["param"=>1]) }}" class="nav-link {{ request()->get("param") == '1' ? 'active' : '' }}">Paid</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('vendor.invoice',["param"=>3]) }}" class="nav-link {{ request()->get("param") == '3' ? 'active' : '' }}">Partially paid</a>
                    </li>
                </ul>
            </div>
            <div>
                {{-- href="{{ route('vendor.invoice_create') }}" --}}
                <a id="add-invo" data-toggle="modal"
                data-target="#invoiceModal" class="btn btn-primary">Add Invoice</a>

            </div>

        </div>

        <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Invoice Number</th>
                <th scope="col">Amount</th>
                <th scope="col">Due Date</th>
                <th scope="col">Status</th>

                <th scope="col">Action</th>

              </tr>
            </thead>
            <tbody>
                @if(!empty($invoices))
                @foreach ($invoices as $index=>$invoice)

                <tr>
                    <th scope="row">{{$index + 1}}</th>
                    <td>{{$invoice->invoice_number}}</td>
                    <td>{{$invoice->amount}}</td>
                    <td>{{\Carbon\Carbon::parse($invoice->due_date)->format('M d,Y')}}</td>
                    <td>
                        <span class="{{$invoice->status == 1 ? 'bg-success' : ($invoice->status == 3 ? 'bg-warning': 'bg-danger')}} p-2 rounded text-white">{{$invoice->status == 1 ? 'Paid' : ($invoice->status == 3 ? 'Patially Paid': 'Un Paid')}}</span>
                        </td>

                    <td>

                        <a href="{{ route('vendor.invoice_create',["id"=>$invoice->id]) }}" class="border p-2 btn btn-primary"><i>View</i></a>
                        <a  href="{{route('vendor.invoice_sendPdf',$invoice->id) }}" class="border p-2 btn btn-info"><i>Send</i></a>
                        @if($invoice->is_send == 2)
                        <button type="button"  class="border p-2 btn btn-success" data-toggle="modal"
                        data-target="#invoiceModal" onclick="handleEdit(this)" data-url="{{route('vendor.editInvoice',['id'=>$invoice->id])}}"><i>Edit</i></button>
                        @endif

                    </td>
                </tr>
                  @endforeach
                @endif
            </tbody>
          </table>
{{$invoices->links()}}
    </div>

    <div class="modal fade" id="invoiceModal" data-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Create Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('vendor.save') }}" enctype="multipart/form-data">
                @csrf
            <div class="modal-body">
                <input type="hidden" name="id" id="ivoice_id">
                    <div class="form-group">
                        <label for="">Invoice Number</label>
                        <input type="text" class="form-control" name="invoice_number" required>
                    </div>

                    <div class="form-group">
                        <label for="">Due Date</label>
                        <input type="date" class="form-control" name="due_date" required>
                    </div>
                    <div class="form-group">
                        <label for="">Amount</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="">Notes</label>
                        <textarea  class="form-control" name="notes" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="file" class="form-control" name="upload_pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" style="display: none;" id="bussiness_popup" data-toggle="modal"
        data-target="#staticBackdrop">
    </button>



    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Bussiness Detail</h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> --}}
                </div>
                <form id="vendorForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Bussiness Name</label>
                            <input type="text" class="form-control" name="bussiness_name" required>
                        </div>

                        <div class="form-group">
                            <label for="">VAT Registeration Number</label>
                            <input type="text" class="form-control" name="vat_number" required>
                        </div>
                        <div class="form-group">
                            <label for="">Bussiness Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary d-none" data-dismiss="modal"
                            id="close-mode">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function() {
            $('#add-invo').click(function(){
                $('#ivoice_id').val('');
                    $('#invoiceModalLabel').text("Create Invoice");
                    $('input[name="invoice_number"]').val('');
                    $('input[name="due_date"]').val('');
                    $('input[name="amount"]').val('');
                    $('textarea[name="notes"]').val('');
            });


            @if (empty(auth()->guard('vendor_users')->user()->vat_number))
                $('#bussiness_popup').trigger('click');
            @endif

            $("#vendorForm").submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('vendor.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response.success);
                        $("#vendorForm")[0].reset(); // Clear form fields
                        $('#close-mode').trigger('click');

                    },
                    error: function(response) {
                        console.log(response)
                        alert("Error saving data!");
                    }
                });
            });

        });
    </script>

    <script>
        const handleEdit = (btn) =>{
            const url = $(btn).data("url");
            $('#invoiceModalLabel').text("Update Invoice");

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#ivoice_id').val(response.invoice.id);

                $('input[name="invoice_number"]').val(response.invoice.invoice_number);
                $('input[name="due_date"]').val(response.invoice.due_date);
                $('input[name="amount"]').val(response.invoice.amount);
                $('textarea[name="notes"]').val(response.invoice.notes);

                $('#invoiceModal').modal('show');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert("Something went wrong while fetching invoice data.");
            }
        });
        }
    </script>
@endpush
