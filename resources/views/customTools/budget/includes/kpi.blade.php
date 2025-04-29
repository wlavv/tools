<div class="col-lg-12">    
    <div class="spacer-10"></div>
</div>
<div class="col-lg-4">
    <div class="card text-center bg-success text-white">
        <div class="card-header" style="font-weight: bolder;font-size: 25px;cursor: pointer;border-bottom: none;" onclick="$('#incomeDetail').toggle()"> 
            <div>RENDIMENTOS</div>
            <div style="font-size: 50px;">3 459.16 €</div>
        </div>
        <div class="card-body" style="display: none; margin: 0 10px 10px 10px;" id="incomeDetail">
            <table style="width: 100%" class="table table-striped shortSpacing">
                <tr>
                    <td class="text-right" style="padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">BRUNO:</td>
                    <td style="padding-left: 5px;">
                        <table style="width: 100%; border: 1px solid #EBEDEF;background-color: #EBEDEF;margin-bottom: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;">Vencimento:</td>
                                <td onclick="updateValue()" style="width: 50%;padding-left: 5px;background-color: #EBEDEF;cursor: pointer;"> 1 905.20 €</td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;">ASM:</td>
                                <td onclick="updateValue()" style="width: 50%;padding-left: 5px;background-color: #EBEDEF;cursor: pointer;"> 150.00 €</td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;">CV:</td>
                                <td onclick="updateValue()" style="width: 50%;padding-left: 5px;background-color: #EBEDEF;cursor: pointer;"> 360.00 €</td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;font-weight: bolder;">TOTAL:</td>
                                <td style="width: 50%;padding-left: 5px;background-color: #EBEDEF;font-weight: bolder;color: darkgreen;"> 2 415.20 €</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">MÁRCIA:</td>
                    <td style="padding-left: 5px;">
                        <table style="width: 100%; border: 1px solid #FFF;margin-bottom: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;">Vencimento:</td>
                                <td onclick="updateValue()" style="width: 50%;padding-left: 5px;cursor: pointer;"> 935.01 €</td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;">Oriflame:</td>
                                <td onclick="updateValue()" style="width: 50%;padding-left: 5px;cursor: pointer;"> 0.00 €</td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;font-weight: bolder;">TOTAL:</td>
                                <td style="width: 50%;padding-left: 5px;font-weight: bolder;color: darkgreen;"> 935.01 €</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">MENINAS:</td>
                    <td style="width: 50%;padding-left: 5px;">
                        <table style="width: 100%; border: 1px solid #FFF;border: 1px solid #EBEDEF;margin-bottom: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;"></td>
                                <td onclick="updateValue()" style="width: 50%;padding-left: 5px;background-color: #EBEDEF;cursor: pointer;"> 108.95 €</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">EXTRA:</td>
                    <td style="width: 50%;padding-left: 5px;">
                        <table style="width: 100%;border: 1px solid #FFF;margin-bottom: 0;padding: 3px;" class="table">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;"></td>
                                <td onclick="updateValue()" style="width: 50%;padding-left: 5px;cursor: pointer;"> 0.00 €</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;font-weight: bolder;">TOTAL:</td>
                    <td style="width: 50%;padding-left: 5px;">
                        <table style="width: 100%;border: 1px solid #EBEDEF;margin-bottom: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td colspan="2" style="text-align: center;padding-left: 5px;background-color: #EBEDEF;color: darkgreen;font-weight: bolder;font-size: 30px;"> 3 459.16 €</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>    
</div>
<div class="col-lg-4">
    <div class="card text-center text-white bg-danger">
        <div class="card-header" style="font-weight: bolder;font-size: 25px;cursor: pointer;border-bottom: none;" onclick="$('#expenseDetail').toggle()">
            <div>DESPESAS</div>
            <div style="font-weight: bolder;font-size: 50px;">2 752.21 €</div>
        </div>
        <div class="card-body" style="display: none; margin: 0 10px 10px 10px;" id="expenseDetail">
            <table style="width: 100%" class="table table-striped shortSpacing">
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">POTES:</td>
                    <td style="width: 50%;padding-left: 5px;">700.00 €</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">FORECAST:</td>
                    <td style="width: 50%;padding-left: 5px;">2 171.48 €</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">EXPENSES:</td>
                    <td style="width: 50%;padding-left: 5px;">2 052.21 €</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">DIFFERENCE:</td>
                    <td style="width: 50%;padding-left: 5px;">- 119.27 €</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">PERCENTAGE:</td>
                    <td style="width: 50%;padding-left: 5px;">- 5.49 %</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">MAX:</td>
                    <td style="width: 50%;padding-left: 5px;">119.27 €</td>
                </tr>
            </table>
        </div>
    </div>
</div>        
<div class="col-lg-4">
    <div class="card text-center text-white bg-info">
        <div class="card-header" style="font-weight: bolder;font-size: 25px;border-bottom: none;" onclick="/**$('#savingsDetail').toggle()**/"> 
            <div>POUPANÇA</div>
            <div style="font-weight: bolder;font-size: 50px;">706.95 €</div>
        </div>
        <div class="card-body" style="display: none; margin: 0 10px 10px 10px;" id="savingsDetail"> </div>
    </div>
</div>     