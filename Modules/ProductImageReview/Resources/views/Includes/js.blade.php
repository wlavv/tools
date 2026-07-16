<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('productImageReview');
    if (!root) return;
    const select = document.getElementById('pirManufacturer');
    const list = document.getElementById('pirList');
    const status = document.getElementById('pirStatus');
    const loader = document.getElementById('pirLoader');
    const sentinel = document.getElementById('pirSentinel');
    let manufacturerId = '', page = 0, loading = false, hasMore = false, requestVersion = 0;
    const el = (tag, cls, text) => { const node=document.createElement(tag); if(cls)node.className=cls; if(text!==undefined)node.textContent=text; return node; };

    function renderProduct(product) {
        const card=el('article','pir-product'), meta=el('div','pir-product-meta'), reference=el('div','pir-reference');
        reference.append(el('i','fa-solid fa-barcode'), document.createTextNode(product.reference));
        meta.append(reference,el('div','pir-name',product.name),el('div','pir-id',`Produto #${product.id_product}`));
        const gallery=el('div','pir-gallery');
        if (!product.images.length) gallery.append(el('div','pir-no-images','Sem imagens'));
        product.images.forEach((image,index) => {
            const link=el('a','pir-image-link'); link.href=image.large_url; link.target='_blank'; link.rel='noopener';
            link.title=`${product.reference} · imagem ${index+1}`;
            const img=document.createElement('img'); img.src=image.thumbnail_url; img.alt=`${product.reference} · ${product.name} · imagem ${index+1}`; img.loading='lazy'; img.decoding='async';
            img.addEventListener('error',()=>{img.remove();link.prepend(el('i','fa-solid fa-triangle-exclamation text-warning fa-2x'));},{once:true});
            link.append(img); if(image.cover)link.append(el('span','pir-cover','Capa')); link.append(el('span','pir-image-number',String(index+1))); gallery.append(link);
        });
        card.append(meta,gallery); list.append(card);
    }

    async function loadNextPage() {
        if(!manufacturerId||loading||!hasMore)return;
        loading=true; loader.hidden=false; const version=requestVersion;
        try {
            const next=page+1, url=new URL(root.dataset.productsUrl,window.location.origin);
            url.searchParams.set('manufacturer_id',manufacturerId); url.searchParams.set('page',String(next));
            const response=await fetch(url,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});
            if(!response.ok)throw new Error(`HTTP ${response.status}`);
            const payload=await response.json(); if(version!==requestVersion)return;
            payload.data.forEach(renderProduct); page=payload.meta.page; hasMore=payload.meta.has_more;
            status.hidden=false; status.replaceChildren(el('i','fa-solid fa-images'),el('span','',`${payload.meta.loaded} de ${payload.meta.total} produtos carregados`));
        } catch(error) {
            if(version===requestVersion){status.hidden=false;status.replaceChildren(el('i','fa-solid fa-circle-exclamation text-danger'),el('span','','Não foi possível carregar os produtos. Tenta novamente.'));}
        } finally { if(version===requestVersion){loading=false;loader.hidden=true;} }
    }

    select.addEventListener('change',()=>{
        requestVersion++; manufacturerId=select.value; page=0; hasMore=Boolean(manufacturerId); loading=false; list.replaceChildren(); loader.hidden=true;
        if(!manufacturerId){status.hidden=false;status.replaceChildren(el('i','fa-solid fa-hand-pointer'),el('span','','Seleciona uma marca para começar.'));return;}
        status.hidden=true; loadNextPage();
    });
    new IntersectionObserver(entries=>{if(entries.some(entry=>entry.isIntersecting))loadNextPage();},{rootMargin:'600px 0px'}).observe(sentinel);
});
</script>
