<div id="multi-panel" class="panel-container cardContainer">
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title">CARD DETAILS</h2>
            <table class="card_detail_table">
                <tr>
                    <td class="card_detail_tag">Name:</td>
                    <td class="card_detail_value">{{$card->name}}</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Type:</td>
                    <td class="card_detail_value">{{$card->card_type}}</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Rarity:</td>
                    <td class="card_detail_value">{{$card->rarity}}</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Set:</td>
                    <td class="card_detail_value"><span style="text-transform: uppercase;font-weight: bolder">{{$card->set_code}}</span> | Mirrodin</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Collectors number:</td>
                    <td class="card_detail_value">{{$card->collector_number}}</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Power:</td>
                    <td class="card_detail_value">{{$card->power}}</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Toughness:</td>
                    <td class="card_detail_value">{{$card->toughness}}</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Flavor text:</td>
                    <td class="card_detail_value">{{$card->flavor_text}}</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Mana Cost:</td>
                    <td class="card_detail_value">Not applicable</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Color:</td>
                    <td class="card_detail_value">Colorless</td>
                </tr>
                <tr>
                    <td class="card_detail_tag">Rules text:</td>
                    <td class="card_detail_value">
                        Cloudpost comes into play tapped
                        <br>Add {C} for each Cloudpost you control.
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title" style="text-align: center;">{{$card->name}}</h2>
            <img src="{{$card->image_url}}" style="max-width: 100%;margin: 0 auto;border-radius: 25px; border: 1px solid #333; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), 0 8px 24px rgba(0, 0, 0, 0.25);">
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