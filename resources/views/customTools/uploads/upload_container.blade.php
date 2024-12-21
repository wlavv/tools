@include("customTools.uploads.js", [ 'upload_accepted_files' => $upload_accepted_files, 'max_files' => $max_files, 'on_success' => $on_success ])

@include("customTools.uploads.css")

<div class="row" id="upload_area" style="display: none;margin-top: 20px;">
    <div class="col-lg-12">
        <form class="dropzone needsclick" id="formDropzone" action="{{route('uploads.upload')}}">
            @csrf
            <input type="hidden" name="upload_filename" value="{{$filename}}">
            <input type="hidden" name="upload_path" value="{{$upload_path}}">
            <div class="dz-message needsclick"> 
                <i class="fa-solid fa-download" style="font-size: 80px; color: dodgerblue;"></i>
                <br><br>
                {!!$message!!}
            </div>
        </form>
        <div id="dzPreviewContainer" style="display: none;">
            {!! $htmlAfterUpload !!}
        </div>
    </div>
</div>