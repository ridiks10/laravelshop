@extends('layout')

@section('content')

  <div class="col-12 col-md-9">
    <div class="row">
        <div class="col-6 col-lg-4">
          <br><br><br>
          <h2>Checkout details: </h2>
          <form action="{{route('shop.pay')}}" method="post">
              <p>Your name: <br> {{ Auth::user()->name }}</p>
              <hr>
              <p>Your email: <br> {{ Auth::user()->email }}</p>
              <hr>
              <p>Your total: <br> {{$total}} $</p>
              <p>Promo Code: <br>
                  <input type="text" name='promo_code' value="{{old('promo_code')}}">
              </p>
              <input type="hidden" name='total' value='<?php echo $total;?>'>
              <input type="hidden" name='method' value="coingate">

              {{ csrf_field() }}
              <button type="submit" class="btn btn-success">Proceed to CoinGate</button>
          </form>
        </div><!--/span-->
      </div>
    </div>

@endsection
