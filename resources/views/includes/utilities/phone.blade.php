<div style="display: inline-flex;">
    <i class="fa-solid fa-phone mobile" style="color: dodgerblue;font-size: 22px;padding-right: 15px;" onclick="makeACall('{{$phone}}')"></i>
    <span style="padding: 0 10px; color: #666;cursor: pointer;display: inline-flex;" onclick="openWhatsappOptions({{$id_customer}})">
        <i class="fa-brands fa-whatsapp" style="color: green;font-size: 28px;"></i>
        <span class="desktop" style="padding-left: 10px;">{{$phone}}</span>
    </span>
</div>