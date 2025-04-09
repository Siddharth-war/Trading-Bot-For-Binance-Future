
<html>
<head>
    <title>senangPay Sample Code</title>
</head>

@if(isset($req))
    <body onload="document.order.submit()">
    <form name="order" method="post" action="https://{{env('APP_MODE')=='live'?'app.senangpay.my':'sandbox.senangpay.my'}}/payment/195161898510584">
        <input type="hidden" name="detail" value="{{$req->detail}}">
        <input type="hidden" name="amount" value="{{$req->amount}}">
        <input type="hidden" name="order_id" value="{{$req->order_id}}">
        <input type="hidden" name="name" value="{{$req->name}}">
        <input type="hidden" name="email" value="{{$req->email}}">
        <input type="hidden" name="phone" value="{{$req->phone}}">
        <input type="hidden" name="hash" value="{{$req->hashed_string}}">
    </form>
    </body>
@elseif(isset($_GET['status_id']) && isset($_GET['order_id']) && isset($_GET['msg']) && isset($_GET['transaction_id']) && isset($_GET['hash']))

    <?php
    $hashed_string = md5($secretkey.urldecode($_GET['status_id']).urldecode($_GET['order_id']).urldecode($_GET['transaction_id']).urldecode($_GET['msg']));

    # if hash is the same then we know the data is valid
    if($hashed_string == urldecode($_GET['hash']))
    {
        # this is a simple result page showing either the payment was successful or failed. In real life you will need to process the order made by the customer
        if(urldecode($_GET['status_id']) == '1')
            echo 'Payment was successful with message: '.urldecode($_GET['msg']);
        else
            echo 'Payment failed with message: '.urldecode($_GET['msg']);
    }
    else
        echo 'Hashed value is not correct';

    ?>
@else
    <body>
    <form method="post" onsubmit="startAction(event,this)">
        @csrf
        <table>
            <tr>
                <td colspan="2">Please fill up the detail below in order to test the payment. Order ID is defaulted to timestamp.</td>
            </tr>
            <tr>
                <td>Detail</td>
                <td>: <input type="text" name="detail" value="" placeholder="Description of the transaction" size="30"></td>
            </tr>
            <tr>
                <td>Amount</td>
                <td>: <input type="text" name="amount" value="" placeholder="Amount to pay, for example 12.20" size="30"></td>
            </tr>
            <tr>
                <td>Order ID</td>
                <td>: <input type="text" name="order_id" value="<?php echo time(); ?>" placeholder="Unique id to reference the transaction or order" size="30"></td>
            </tr>
            <tr>
                <td>Customer Name</td>
                <td>: <input type="text" name="name" value="" placeholder="Name of the customer" size="30"></td>
            </tr>
            <tr>
                <td>Customer Email</td>
                <td>: <input type="text" name="email" value="" placeholder="Email of the customer" size="30"></td>
            </tr>
            <tr>
                <td>Customer Contact No</td>
                <td>: <input type="text" name="phone" value="" placeholder="Contact number of customer" size="30"></td>
            </tr>
            <tr>
                <td><input type="submit" value="Submit"></td>
            </tr>
        </table>
    </form>

    </body>
@endif

@if ($errors->any())

    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
</html>
