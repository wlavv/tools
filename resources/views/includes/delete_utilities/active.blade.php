@if($active)
    <i id="active_{{$id}}" class="fa-solid fa-check"  style="color: green; cursor: pointer;" onclick="editItem({{$id}}, 0, '{{$route}}')"></i>
@else
    <i id="active_{{$id}}" class="fa-solid fa-xmark"  style="color: red;   cursor: pointer;"   onclick="editItem({{$id}}, 1, '{{$route}}')"></i>
@endif