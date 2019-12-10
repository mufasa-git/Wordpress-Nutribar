'use strict';

jQuery(document).on('ready', function () {
    jQuery('.product-type-woosb').each(function () {
        woosb_init(jQuery(this));
    });
});

jQuery(document).on('found_variation', function (e, t) {
    var $woosb_wrap = jQuery(e['target']).closest('.product-type-woosb');
    var $woosb_products = jQuery(e['target']).closest('.woosb-products');
    var $woosb_product = jQuery(e['target']).closest('.woosb-product');

    if ($woosb_product.length) {
        if (t['image']['url'] && t['image']['srcset']) {
            // change image
            $woosb_product.find('.woosb-thumb-ori').hide();
            $woosb_product.find('.woosb-thumb-new').html('<img src="' + t['image']['url'] + '" srcset="' + t['image']['srcset'] + '"/>').show();
        }
        if (t['price_html']) {
            // change price
            $woosb_product.find('.woosb-price-ori').hide();
            $woosb_product.find('.woosb-price-new').html(t['price_html']).show();
        }
        if (t['is_purchasable']) {
            // change stock notice
            if (t['is_in_stock']) {
                $woosb_products.next('p.stock').show();
                $woosb_product.attr('data-id', t['variation_id']);
                $woosb_product.attr('data-price', t['display_price']);
            } else {
                $woosb_products.next('p.stock').hide();
                $woosb_product.attr('data-id', 0);
                $woosb_product.attr('data-price', 0);
            }

            // change availability text
            jQuery(e['target']).closest('.variations_form').find('p.stock').remove();
            if (t['availability_html'] != '') {
                jQuery(e['target']).closest('.variations_form').append(t['availability_html']);
            }
        }
        if (t['variation_description'] != '') {
            $woosb_product.find('.woosb-variation-description').html(t['variation_description']).show();
        } else {
            $woosb_product.find('.woosb-variation-description').html('').hide();
        }

        if (woosb_vars.change_image == 'no') {
            // prevent changing the main image
            jQuery(e['target']).closest('.variations_form').trigger('reset_image');
        }

        woosb_init($woosb_wrap);
    }
});

jQuery(document).on('reset_data', function (e) {
    var $woosb_wrap = jQuery(e['target']).closest('.product-type-woosb');
    var $woosb_product = jQuery(e['target']).closest('.woosb-product');

    if ($woosb_product.length) {
        // reset thumb
        $woosb_product.find('.woosb-thumb-new').hide();
        $woosb_product.find('.woosb-thumb-ori').show();

        // reset price
        $woosb_product.find('.woosb-price-new').hide();
        $woosb_product.find('.woosb-price-ori').show();

        // reset stock
        jQuery(e['target']).closest('.variations_form').find('p.stock').remove();

        // reset desc
        $woosb_product.find('.woosb-variation-description').html('').hide();

        // reset id
        $woosb_product.attr('data-id', 0);
        $woosb_product.attr('data-price', 0);

        woosb_init($woosb_wrap);
    }
});

jQuery(document).on('click touch', '.single_add_to_cart_button', function (e) {
    var $this = jQuery(this);
    var $woosb_wrap = $this.closest('.product-type-woosb');
    var $woosb_products = $woosb_wrap.find('.woosb-products');

    if ($this.hasClass('woosb-disabled')) {
        if ($this.hasClass('woosb-selection')) {
            alert(woosb_vars.alert_selection);
        } else if ($this.hasClass('woosb-empty')) {
            alert(woosb_vars.alert_empty);
        } else if ($this.hasClass('woosb-min')) {
            alert(woosb_vars.alert_min.replace('[min]', $woosb_products.attr('data-min')));
        } else if ($this.hasClass('woosb-max')) {
            alert(woosb_vars.alert_max.replace('[max]', $woosb_products.attr('data-max')));
        }
        e.preventDefault();
    }
});

jQuery(document).on('keyup change', '.woosb-qty .qty', function () {
    var $this = jQuery(this);
    var $woosb_wrap = $this.closest('.product-type-woosb');
    var qty = parseInt($this.val());
    var min_qty = parseInt($this.attr('min'));
    var max_qty = parseInt($this.attr('max'));

    if ((qty == '') || isNaN(qty)) {
        qty = 0;
    }

    if (!isNaN(min_qty) && (
        qty < min_qty
    )) {
        qty = min_qty;
    }

    if (!isNaN(max_qty) && (
        qty > max_qty
    )) {
        qty = max_qty;
    }

    $this.val(qty);
    $this.closest('.woosb-product').attr('data-qty', qty);

    woosb_init($woosb_wrap);
});

jQuery(document).on('woosq_loaded', function () {
    // product bundles in quick view popup
    woosb_init(jQuery('#woosq-popup .product-type-woosb'));
});

function woosb_init($woosb_wrap) {
    var total = 0;
    var is_selection = false;
    var is_empty = true;
    var is_min = false;
    var is_max = false;

    var $woosb_products = $woosb_wrap.find('.woosb-products');
    var $woosb_btn = $woosb_wrap.find('.single_add_to_cart_button');

    $woosb_products.find('.woosb-product').each(function () {
        var $this = jQuery(this);
        if ((
            $this.attr('data-qty') > 0
        ) && (
            $this.attr('data-id') == 0
        )) {
            is_selection = true;
        }
        if ($this.attr('data-qty') > 0) {
            is_empty = false;
            total += parseInt($this.attr('data-qty'));
        }
    });

    // check min
    if ((
        $woosb_products.attr('data-optional') == 'yes'
    ) && $woosb_products.attr('data-min') && (
        total < parseInt($woosb_products.attr('data-min'))
    )) {
        is_min = true;
    }

    // check max
    if ((
        $woosb_products.attr('data-optional') == 'yes'
    ) && $woosb_products.attr('data-max') && (
        total > parseInt($woosb_products.attr('data-max'))
    )) {
        is_max = true;
    }

    if (is_selection || is_empty || is_min || is_max) {
        $woosb_btn.addClass('woosb-disabled');
        if (is_selection) {
            $woosb_btn.addClass('woosb-selection');
        } else {
            $woosb_btn.removeClass('woosb-selection');
        }
        if (is_empty) {
            $woosb_btn.addClass('woosb-empty');
        } else {
            $woosb_btn.removeClass('woosb-empty');
        }
        if (is_min) {
            $woosb_btn.addClass('woosb-min');
        } else {
            $woosb_btn.removeClass('woosb-min');
        }
        if (is_max) {
            $woosb_btn.addClass('woosb-max');
        } else {
            $woosb_btn.removeClass('woosb-max');
        }
    } else {
        $woosb_btn.removeClass('woosb-disabled woosb-selection woosb-empty woosb-min woosb-max');
    }

    woosb_calc_price($woosb_wrap);
    woosb_save_ids($woosb_wrap);
}

function woosb_calc_price($woosb_wrap) {
    var total = 0;
    var total_sale = 0;
    var $woosb_products = $woosb_wrap.find('.woosb-products');
    var $woosb_total = $woosb_wrap.find('.woosb-total');

    $woosb_products.find('.woosb-product').each(function () {
        var $this = jQuery(this);
        if ($this.attr('data-price') > 0) {
            total += $this.attr('data-price') * $this.attr('data-qty');
        }
    });

    var _discount = parseFloat($woosb_products.attr('data-discount'));
    var _discount_amount = parseFloat($woosb_products.attr('data-discount-amount'));
    var _saved = '';

    if ((
        _discount_amount > 0
    ) && (
        _discount_amount < total
    )) {
        total_sale = total - _discount_amount;
        _saved = woosb_format_money(_discount_amount);
    } else if ((
        _discount > 0
    ) && (
        _discount < 100
    )) {
        total_sale = total * (
            100 - _discount
        ) / 100;
        _saved = woosb_round(_discount) + '%';
    } else {
        total_sale = total;
    }

    var total_html = woosb_price_html(total, total_sale);

    if (_saved != '') {
        total_html += ' <small class="woocommerce-price-suffix">' + woosb_vars.saved_text.replace('[d]', _saved) + '</small>';
    }

    $woosb_total.html(woosb_vars.price_text + ' ' + total_html).slideDown();

    if ((
        woosb_vars.change_price == 'yes'
    ) && (
        $woosb_products.attr('data-fixed-price') == 'no'
    ) && (
        (
            $woosb_products.attr('data-variables') == 'yes'
        ) || (
            $woosb_products.attr('data-optional') == 'yes'
        )
    )) {
        // change the main price
        $woosb_wrap.find('.summary > .price').html(total_html);
    }

    jQuery(document).trigger('woosb_calc_price', [total_sale, total, total_html]);
}

function woosb_save_ids($woosb_wrap) {
    var woosb_ids = Array();
    var $woosb_products = $woosb_wrap.find('.woosb-products');
    var $woosb_ids = $woosb_wrap.find('.woosb-ids');

    $woosb_products.find('.woosb-product').each(function () {
        var $this = jQuery(this);
        if ((
            $this.attr('data-id') > 0
        ) && (
            $this.attr('data-qty') > 0
        )) {
            woosb_ids.push($this.attr('data-id') + '/' + $this.attr('data-qty'));
        }
    });

    $woosb_ids.val(woosb_ids.join(','));
}

function woosb_round(num) {
    return +(
        Math.round(num + "e+2") + "e-2"
    );
}

function woosb_format_money(number, places, symbol, thousand, decimal) {
    number = number || 0;
    places = !isNaN(places = Math.abs(places)) ? places : 2;
    symbol = symbol !== undefined ? symbol : "$";
    thousand = thousand || ",";
    decimal = decimal || ".";
    var negative = number < 0 ? "-" : "",
        i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
        j = 0;
    if (i.length > 3) {
        j = i.length % 3;
    }
    return symbol + negative + (
        j ? i.substr(0, j) + thousand : ""
    ) + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (
        places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : ""
    );
}

function woosb_format_price(price) {
    var price_html = '<span class="woocommerce-Price-amount amount">';
    var price_formatted = woosb_format_money(price, woosb_vars.price_decimals, '', woosb_vars.price_thousand_separator, woosb_vars.price_decimal_separator);

    switch (woosb_vars.price_format) {
        case '%1$s%2$s':
            //left
            price_html += '<span class="woocommerce-Price-currencySymbol">' + woosb_vars.currency_symbol + '</span>' + price_formatted;
            break;
        case '%1$s %2$s':
            //left with space
            price_html += '<span class="woocommerce-Price-currencySymbol">' + woosb_vars.currency_symbol + '</span> ' + price_formatted;
            break;
        case '%2$s%1$s':
            //right
            price_html += price_formatted + '<span class="woocommerce-Price-currencySymbol">' + woosb_vars.currency_symbol + '</span>';
            break;
        case '%2$s %1$s':
            //right with space
            price_html += price_formatted + ' <span class="woocommerce-Price-currencySymbol">' + woosb_vars.currency_symbol + '</span>';
            break;
        default:
            //default
            price_html += '<span class="woocommerce-Price-currencySymbol">' + woosb_vars.currency_symbol + '</span> ' + price_formatted;
    }

    price_html += '</span>';

    return price_html;
}

function woosb_price_html(regular_price, sale_price) {
    var price_html = '';

    if (sale_price < regular_price) {
        price_html = '<del>' + woosb_format_price(regular_price) + '</del> <ins>' + woosb_format_price(sale_price) + '</ins>';
    } else {
        price_html = woosb_format_price(regular_price);
    }

    return price_html;
}