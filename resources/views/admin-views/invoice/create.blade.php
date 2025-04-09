@extends('layouts.admin.app')

@section('title', translate('Dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" src="{{ asset('public/assets/admin') }}/vendor/apex/apexcharts.css">
    </link>
@endpush

@section('content')

    <section class="py-3 py-md-5">
        <div class="container ml-2">
            <div class="row justify-content-center d-flex">
                <div class="col-6 border p-2">
                    <div class="row gy-3 mb-3">
                        <div class="col-6">
                            <h2 class="text-uppercase text-endx m-0">Invoice</h2>
                        </div>
                        <div class="col-6">
                        </div>
                        <div class="col-12">
                            <h4>From</h4>
                            <address>
                                <strong>{{ $user->first_name . ' ' . $user->last_name }}</strong><br>
                                Phone: {{ $user->phone_number }}<br>
                                Email: {{ $user->email }}
                            </address>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 col-md-8">
                            <h4>Bill To</h4>
                            <address>
                                <strong>{{ $admin->f_name . ' ' . $admin->l_name }}</strong><br>
                                Phone: {{ $admin->phone }}<br>
                                Email: {{ $admin->email }}
                            </address>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <h4 class="row">
                                <span class="col-6">Invoice Id</span>
                                <span class="col-6 text-sm-end">{{ $invoices->invoice_id ?? null }}</span>
                            </h4>
                            <h4 class="row">
                                <span class="col-6">Invoice #</span>
                                <span class="col-6 text-sm-end">{{ $invoices->invoice_number ?? null }}</span>
                            </h4>
                            <div class="row">
                                <span class="col-6">Invoice Date</span>
                                <span class="col-6 text-sm-end">{{ \Carbon\Carbon::now()->format('m/d/Y') }}</span>
                                <span class="col-6">Due Date</span>
                                <span class="col-6 text-sm-end">{{ \Carbon\Carbon::parse($invoices->due_date)->format('m/d/Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-uppercase">Amount</th>
                                            <th scope="col" class="text-uppercase">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider">
                                        <tr>
                                            <th scope="row">{{$invoices?->amount}}</th>
                                            <td>
                                                {{$invoices?->notes}}
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-primary">Pay</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6  p-2">
                    <h2>PREVIEW</h2>
                    <canvas id="pdf-canvas" width="100" height="100"></canvas>

                </div>


            </div>
        </div>
    </section>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

    <script>
        // Initialize PDF.js library
        const pdfjsLib = window['pdfjs-dist/build/pdf'];

        // Specify the path to your PDF file
        const pdfPath = "{{ asset('/storage/app/public/' . $invoices->upload_pdf) }}";

        // Load the PDF document
        pdfjsLib.getDocument(pdfPath).promise.then(function(pdf) {
            // Fetch the first page (you can change this to render other pages)
            pdf.getPage(1).then(function(page) {
                // Get the canvas element where we will render the PDF page
                const canvas = document.getElementById('pdf-canvas');
                const context = canvas.getContext('2d');

                // Set the scale for the PDF rendering (adjust for resolution)
                const scale = 1.5;
                const viewport = page.getViewport({ scale: scale });

                // Set canvas size based on PDF page size
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                // Render the page onto the canvas
                page.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise.then(function() {
                    console.log("PDF page rendered to canvas successfully.");
                });
            });
        });
    </script>

@endsection
