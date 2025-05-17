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
                    <td class="card_detail_tag">Collectors #:</td>
                    <td class="card_detail_value">{{$card->collector_number}}</td>
                </tr>
                @if(!is_null($card->power ))
                <tr>
                    <td class="card_detail_tag">Power:</td>
                    <td class="card_detail_value">{{$card->power}}</td>
                </tr>
                @endif

                @if(!is_null($card->power ))
                <tr>
                    <td class="card_detail_tag">Toughness:</td>
                    <td class="card_detail_value">{{$card->toughness}}</td>
                </tr>
                @endif

                @if(!is_null($card->flavor_text ))
                <tr>
                    <td class="card_detail_tag">Flavor text:</td>
                    <td class="card_detail_value">{{$card->flavor_text}}</td>
                </tr>
                @endif

                @if(!is_null($card->mana_cost ))
                <tr>
                    <td class="card_detail_tag">Mana Cost:</td>
                    <td class="card_detail_value">Not applicable</td>
                </tr>
                @endif
                <tr>
                    <td class="card_detail_tag">Color:</td>
                    <td class="card_detail_value">Colorless</td>
                </tr>

                @if(!is_null($card->rules_text ))

                <tr>
                    <td class="card_detail_tag">Rules text:</td>
                    <td class="card_detail_value"> {{$card->rules_text}}</td>
                </tr>

                @endif
            </table>
        </div>
    </div>
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title" style="text-align: center;">{{$card->name}}</h2>
            <div class="cardCenterContainer" id="cardImageContainer">
                <img src="{{$card->image_url}}" style="max-width: 100%;margin: 0 auto;border-radius: 25px; border: 1px solid #333; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), 0 8px 24px rgba(0, 0, 0, 0.25);">
            </div>
            <div class="cardCenterContainer" id="cardPriceContainer">
                price
            </div>
            <div class="cardCenterContainer" id="cardBanningContainer">
                banning
            </div>
            <div style="margin: 20px 0px;">
                <div onclick="$('.cardCenterContainer').css('display', 'none'); $('#cardImageContainer').css('display', 'block');" style="width: 33%; float: left; text-align: center; font-size: 26px; background-color: #FFF; color: #333;border: 1px solid #FFF;padding: 10px 0;"><i class="fa-solid fa-image"></i></div>
                <div onclick="$('.cardCenterContainer').css('display', 'none'); $('#cardPriceContainer').css('display', 'block');" style="width: 32%; float: left; text-align: center; font-size: 26px; background-color: transparent; color: #fff;border: 1px solid #FFF;padding: 10px 0;"><i class="fa-solid fa-euro-sign"></i></div>
                <div onclick="$('.cardCenterContainer').css('display', 'none'); $('#cardBanningContainer').css('display', 'block');" style="width: 33%; float: left; text-align: center; font-size: 26px; background-color: transparent; color: #fff;border: 1px solid #FFF;padding: 10px 0;"><i class="fa-solid fa-ban"></i></div>
            </div>
        </div>
    </div>
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title">KNOWN DECKS</h2>
        </div>
        <div class="panel">
            <h2 class="panel_title">ARTICLES | REVIEWS | VIDEOS</h2> 
            <div>
                <div style="text-align: left;">
                    <a href="https://www.youtube.com/results?search_query={{urlencode($card->name)}}" target="_blank" title="{{$card->name}}" style="verticel-align: middle;text-transform: uppercase;">
                        <div style="width: 50px; float: left;">
                            <svg style="width: 60px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 24 24" enable-background="new 0 0 24 24" xml:space="preserve" focusable="false" aria-hidden="true" style="pointer-events: none; display: inherit; width: 100%; height: 100%;">
                                <g>
                                    <path fill="#FF0033" d="M21.58,7.19c-0.23-0.86-0.91-1.54-1.77-1.77C18.25,5,12,5,12,5S5.75,5,4.19,5.42   C3.33,5.65,2.65,6.33,2.42,7.19C2,8.75,2,12,2,12s0,3.25,0.42,4.81c0.23,0.86,0.91,1.54,1.77,1.77C5.75,19,12,19,12,19   s6.25,0,7.81-0.42c0.86-0.23,1.54-0.91,1.77-1.77C22,15.25,22,12,22,12S22,8.75,21.58,7.19z"></path>
                                    <polygon fill="#FFFFFF" points="10,15 15,12 10,9  "></polygon>
                                </g>
                            </svg>
                        </div>
                        <div style="width: calc( 100% - 50px); float: left;">{{$card->name}}: </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="panel">
            <h2 class="panel_title">OFFICIAL INFO</h2>            
        </div>
    </div>
</div>