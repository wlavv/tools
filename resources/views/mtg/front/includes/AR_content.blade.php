<div id="multi-panel" class="panel-container cardContainer">
    <div class="panel_parent">
        <div class="panel">
            <h2 class="panel_title">Card details</h2>
            <table class="card_detail_table">
                @if(!is_null($card->mana_cost))
                    <tr>
                        <td class="card_detail_tag">Mana Cost:</td>
                        <td class="card_detail_value">{!!$card_cost!!}</td>
                    </tr>
                @endif

                <tr>
                    <td class="card_detail_tag">Color:</td>
                    <td class="card_detail_value">{{$card_color}}</td>
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
                    <td class="card_detail_value"><span style="text-transform: uppercase;font-weight: bolder">{{$card->set_code}}</span> | {{$card->set_name}}</td>
                </tr>

                @if(!is_null($card->oracle_text ))
                        <tr>
                            <td class="card_detail_tag">Oracle text:</td>
                            <td class="card_detail_value"> {{$card->oracle_text}}</td>
                        </tr>
                @endif

                @if(!is_null($card->flavor_text ))
                    <tr>
                        <td class="card_detail_tag">Flavor text:</td>
                        <td class="card_detail_value">{{$card->flavor_text}}</td>
                    </tr>
                @endif
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

                <tr>
                    <td class="card_detail_tag">Release date:</td>
                    <td class="card_detail_value">{{$card->released_at}}</td>
                </tr>

                <tr>
                    <td class="card_detail_tag">Collectors #:</td>
                    <td class="card_detail_value">{{$card->collector_number}}</td>
                </tr>

                @if(!is_null($card->artist ))
                    <tr>
                        <td class="card_detail_tag">Artist:</td>
                        <td class="card_detail_value">{{$card->artist}}</td>
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
            <h2 class="panel_title">Known decks</h2>
            <div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="https://archidekt.com/search/decks?name={{$card->name}}" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/Archidekt.png?t=9" alt="ARCHIDEKT" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">ARCHIDEKT</span> </div>
                    </a>
                </div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="https://cubecobra.com/tool/searchcards?f={{urlencode($card->name)}}&p=0&s=Elo&d=descending&di=names" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/CubeCobra.png" alt="CubeCobra" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">CubeCobra</span> </div>
                    </a>
                </div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="https://moxfield.com/search/cards?q={{urlencode($card->name)}}" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/Moxfield.png" alt="Moxfield" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">Moxfield</span> </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="panel">
            <h2 class="panel_title">Articles | Reviews | Videos</h2> 
            <div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="https://www.youtube.com/results?search_query={{urlencode($card->name)}}" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/youtube.png?t=9" alt="MTG WIKI" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">YOUTUBE</span> </div>
                    </a>
                </div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="https://www.reddit.com/r/EDH/search/?q={{urlencode($card->name)}}" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/reddit.png" alt="REDDIT" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">REDDIT</span> </div>
                    </a>
                </div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="https://commanderspellbook.com/search/?q={{urlencode($card->name)}}" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/commanderspellbook.png" alt="COMMANDER SPELLBOCK" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">COMMANDER SPELLBOCK</span> </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="panel">
            <h2 class="panel_title">Official info</h2>  
            <div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="{{$card->gatherer}}" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/gatherer.png?t=9" alt="GATHERER" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">GATHERER</span> </div>
                    </a>
                </div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="https://mtg.wiki/index.php?title={{str_replace(' ', '_', $card->name)}}&redirect=yes" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/mtgwiki.png" alt="MTG WIKI" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">MTG WIKI</span> </div>
                    </a>
                </div>
                <div style="text-align: center;width: 33%;float: left;">
                    <a href="{{$card->scryfall_uri}}&redirect=yes" target="_blank" title="{{$card->name}}" style="text-decoration-line: none;verticel-align: middle;text-transform: uppercase;">
                        <div style="text-align: center;">
                            <img src="/images/mtg/custom_images/scryfall.png" alt="SCRYFALL" style="width: 150px;">
                        </div>
                        <div style="padding: 11px 0;"> <span style="padding-left: 5px;color: #FFF;">SCRYFALL</span> </div>
                    </a>
                </div>
            </div>           
        </div>
    </div>
</div>