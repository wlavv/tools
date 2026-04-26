<div class="input-group mb-3">
    <span class="input-group-text" id="basic-addon1" style="width: 110px;overflow: hidden;">{{$name}}</span>
    <textarea type="text" class="form-control" aria-label="{{$field}}" aria-describedby="basic-addon1" name="{{$field}}" value="{{ (isset($value)) ? $value : ''}}" id="{{$id}}"></textarea>
</div>