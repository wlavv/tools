<?php
namespace Modules\ProductImageReview\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductImageReviewService
{
    public function asmManufacturers(): Collection
    {
        return $this->db()->table($this->table('manufacturer').' as m')
            ->join($this->table('product').' as p','p.id_manufacturer','=','m.id_manufacturer')
            ->join($this->table('product_shop').' as ps',fn($j)=>$j->on('ps.id_product','=','p.id_product')->where('ps.id_shop',$this->shopId()))
            ->where('m.active',1)->select('m.id_manufacturer','m.name')
            ->selectRaw('COUNT(DISTINCT p.id_product) AS product_count')
            ->groupBy('m.id_manufacturer','m.name')->orderBy('m.name')->get();
    }

    public function products(int $manufacturerId,int $page): array
    {
        abort_unless($this->isAsmManufacturer($manufacturerId),404);
        $perPage=(int)config('product-image-review.per_page',10);
        $query=$this->db()->table($this->table('product').' as p')
            ->join($this->table('product_shop').' as ps',fn($j)=>$j->on('ps.id_product','=','p.id_product')->where('ps.id_shop',$this->shopId()))
            ->leftJoin($this->table('product_lang').' as pl',fn($j)=>$j->on('pl.id_product','=','p.id_product')->where('pl.id_shop',$this->shopId())->where('pl.id_lang',(int)config('product-image-review.english_language_id',2)))
            ->where('p.id_manufacturer',$manufacturerId)
            ->select('p.id_product','p.reference','pl.name')
            ->orderByRaw("CASE WHEN p.reference IS NULL OR TRIM(p.reference) = '' THEN 1 ELSE 0 END")
            ->orderBy('p.reference')->orderBy('p.id_product');
        $total=(clone $query)->count();
        $products=$query->forPage($page,$perPage)->get();
        $images=$this->imagesFor($products->pluck('id_product')->map(fn($id)=>(int)$id)->all());
        $items=$products->map(fn($product)=>[
            'id_product'=>(int)$product->id_product,
            'reference'=>trim((string)$product->reference)?:'—',
            'name'=>trim((string)$product->name)?:'Sem nome em EN',
            'images'=>$images->get((int)$product->id_product,collect())->values()->all(),
        ])->values()->all();
        return ['data'=>$items,'meta'=>[
            'page'=>$page,'per_page'=>$perPage,'total'=>$total,
            'loaded'=>min($page*$perPage,$total),'has_more'=>$page*$perPage<$total,
        ]];
    }

    private function imagesFor(array $productIds): Collection
    {
        if($productIds===[])return collect();
        return $this->db()->table($this->table('image').' as i')
            ->join($this->table('image_shop').' as ims',fn($j)=>$j->on('ims.id_image','=','i.id_image')->where('ims.id_shop',$this->shopId()))
            ->whereIn('i.id_product',$productIds)->orderBy('i.id_product')->orderBy('i.position')
            ->get(['i.id_product','i.id_image','i.position','ims.cover'])
            ->groupBy(fn($image)=>(int)$image->id_product)
            ->map(fn(Collection $rows)=>$rows->map(fn($image)=>[
                'id_image'=>(int)$image->id_image,'position'=>(int)$image->position,'cover'=>(bool)$image->cover,
                'thumbnail_url'=>$this->imageUrl((int)$image->id_image,(string)config('product-image-review.thumbnail_type','small_default')),
                'large_url'=>$this->imageUrl((int)$image->id_image,(string)config('product-image-review.large_image_type','large_default')),
            ]));
    }

    private function imageUrl(int $idImage,string $type): string
    {
        return config('product-image-review.store_url').'/img/p/'.implode('/',str_split((string)$idImage)).'/'.$idImage.'-'.$type.'.jpg';
    }

    private function isAsmManufacturer(int $manufacturerId): bool
    {
        return $this->db()->table($this->table('product').' as p')
            ->join($this->table('product_shop').' as ps',fn($j)=>$j->on('ps.id_product','=','p.id_product')->where('ps.id_shop',$this->shopId()))
            ->where('p.id_manufacturer',$manufacturerId)->exists();
    }

    private function db(){return DB::connection((string)config('product-image-review.connection','mysql'));}
    private function shopId(): int{return (int)config('product-image-review.asm_shop_id',2);}
    private function table(string $name): string
    {
        $table=(string)config('product-image-review.table_prefix','ps_').$name;
        $database=trim((string)config('product-image-review.database',''));
        return $database===''?$table:$database.'.'.$table;
    }
}
