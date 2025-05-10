<div id="multi-panel" class="panel-container cardContainer">
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title">CARD DETAILS</h2>
            <table>
                <tr>
                    <td>Name:</td>
                    <td>{{$card->name}}</td>
                </tr>
                <tr>
                    <td>Type:</td>
                    <td>{{$card->card_type}}</td>
                </tr>
                <tr>
                    <td>Rarity:</td>
                    <td>{{$card->rarity}}</td>
                </tr>
                <tr>
                    <td>Set:</td>
                    <td><span style="text-transform: uppercase;font-weight: bolder">{{$card->set_code}}</span> | Mirrodin</td>
                </tr>
                <tr>
                    <td>Collectors number:</td>
                    <td>{{$card->collector_number}}</td>
                </tr>
                <tr>
                    <td>Power:</td>
                    <td>{{$card->power}}</td>
                </tr>
                <tr>
                    <td>Toughness:</td>
                    <td>{{$card->toughness}}</td>
                </tr>
                <tr>
                    <td>Flavor text:</td>
                    <td>{{$card->flavor_text}}</td>
                </tr>
                <tr>
                    <td>Mana Cost:</td>
                    <td>Not applicable</td>
                </tr>
                <tr>
                    <td>Color:</td>
                    <td>Colorless</td>
                </tr>
                <tr>
                    <td>Rules text:</td>
                    <td>
                        Cloudpost comes into play tapped
                        <br>Add {C} for each Cloudpost you control.
                    </td>
                </tr>
            </table>
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