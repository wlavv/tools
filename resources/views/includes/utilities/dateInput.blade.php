<div class="input-group mb-3">
    <span class="input-group-text" id="basic-addon1" style="width: 110px;overflow: hidden;">{{$name}}</span>
    <input type="date" class="form-control" placeholder="{{$placeholder}}" aria-label="{{$field}}" aria-describedby="basic-addon1" name="{{$field}}" value="{{ (isset($value)) ? $value : ''}}" id="{{$id}}">
</div>
