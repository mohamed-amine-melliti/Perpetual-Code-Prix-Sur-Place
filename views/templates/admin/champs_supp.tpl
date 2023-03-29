<div class="product-prices">
    {if isset($product.prix_sur_place) && $product.prix_sur_place}
        <div class="price">
            <span class="label">{l s='Prix sur place'}</span>
            <span class="value">{convertPrice price=$product.prix_sur_place}</span>
        </div>
    {/if}
</div>