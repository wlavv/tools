<div class="navbar navbar-light customPanel" id="editCustomersContainer">
    <form style="margin: 0;" method="POST" action="{{route('customers.update', $customer->id_customer)}}">
        @csrf
        @method('PUT') 
        <input type="hidden" name="id_customer" class="form-control" id="lastname" value="{{$customer->id_customer}}">
        <div class="row">
            @include('includes.utilities.textInput', [ 'field' => 'firstname', 'name' => 'FIRSTNAME', 'placeholder' => 'FIRSTNAME', 'id' => 'firstname' ])
            @include('includes.utilities.textInput', [ 'field' => 'lastname',  'name' => 'LASTNAME',  'placeholder' => 'LASTNAME',  'id' => 'lastname' ])
            @include('includes.utilities.textInput', [ 'field' => 'email',     'name' => 'EMAIL', 'placeholder' => 'EMAIL ADDRESS', 'id' => 'email' ])
            @include('includes.utilities.textInput', [ 'field' => 'phone',     'name' => 'PHONE', 'placeholder' => 'PHONE', 'id' => 'phone' ])
            <div class="col-lg-8"> </div>
            <div class="col-lg-4"> <button class="btn btn-warning" type="submit" style="width: 100%">UPDATE</button> </div>
        </div>
    </form>
</div>  