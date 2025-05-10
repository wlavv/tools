<div id="multi-panel" class="panel-container cardContainer">
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title">CARD DETAILS</h2>
            <ul class="ul_items">
                <li>
                    <span style="text-transform: uppercase; float: left;">Name: </span>
                    <span style="float: left;">{{$card->name}} </span>
                </li>

                <li>
                    <span style="text-transform: uppercase; float: left;">Type: </span>
                    <span style="float: left;">{{$card->card_type}} </span>
                </li>

                <li>
                    <span style="text-transform: uppercase; float: left;">Rarity: </span>
                    <span style="float: left;">{{$card->rarity}} </span>
                </li>
                <li>
                    <span style="text-transform: uppercase; float: left;">Set: </span>
                    <span style="float: left;"> <span style="text-transform: uppercase;font-weight: bolder">{{$card->set_code}}</span> | Mirrodin </span>
                </li>

                <li>
                    <span style="text-transform: uppercase; float: left;">Collectors number: </span>
                    <span style="float: left;">{{$card->collector_number}} </span>
                </li>

                <li>
                    <span style="text-transform: uppercase; float: left;">Power: </span>
                    <span style="float: left;">{{$card->power}} </span>
                </li>

                <li>
                    <span style="text-transform: uppercase; float: left;">Toughness: </span>
                    <span style="float: left;">{{$card->toughness}} </span>
                </li>
                <li>
                    <span style="text-transform: uppercase; float: left;">Flavor text: </span>
                    <span style="float: left;"> {{$card->flavor_text}} </span>
                </li>
                <li>
                    <span style="text-transform: uppercase; float: left;">Mana Cost: </span>
                    <span>Not applicable</span>
                    {{--
                    <img src="/images/mtg/custom_images/C.svg" style="width: 35px; float: left;">
                    --}}
                </li>

                <li>
                    <span style="text-transform: uppercase; float: left;">Color: </span>
                    <span style="float: left;">Colorless </span>
                </li>

                <li>
                    <span style="text-transform: uppercase; float: left;">Rules text: </span>
                    <span style="float: left;"> 
                        Cloudpost comes into play tapped
                        <br>Add {C} for each Cloudpost you control.
                    </span>
                </li>
            </ul>
        </div>
    </div>
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title" style="text-align: center;">CLOUDPOST</h2>
            <img src="{{$card->image_url}}" style="max-width: 100%;margin: 0 auto;border-radius: 25px;">
        </div>
    </div>
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title">KNOWN DECKS</h2>
        </div>
        <div class="panel">
            <h2 class="panel_title">ARTICLES</h2>            
        </div>
        <div class="panel">
            <h2 class="panel_title">OFFICIAL INFO</h2>            
        </div>
    </div>
</div>