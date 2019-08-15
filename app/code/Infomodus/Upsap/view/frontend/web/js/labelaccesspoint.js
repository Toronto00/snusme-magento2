define([
    'ko',
    'jquery',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'mage/url'
], function (ko, $, selectShippingMethodAction, checkoutData, customer, quote, urlBuilder) {
    'use strict';

    var rvaPopupApCSS = '', rvaPopupApEdit = "true", isSelectedUpsapMethod = false;
    $("body").on('click', '#div_closebutton_access_points7172837', function () {
        closePopapMapRVA();
    });

    $.post(urlBuilder.build("upsap/index/getCssLink"), {}, function (o) {
        if (o) {
            if (o.link) {
                rvaPopupApCSS = o.link;
            }
            if (o.edit) {
                rvaPopupApEdit = o.edit;
            }
        }
    }, 'json');

    function startAction() {
        if (($('body input[id^="s_method_upsap_"]:checked') && $('body input[id^="s_method_upsap_"]:checked').length > 0)
            || ($('body input[value^="upsap_"]:checked') && $('body input[value^="upsap_"]:checked').length > 0)) {
            if (quote.shippingAddress()) {
                var address = {};
                var addressItems = quote.shippingAddress();
                if (addressItems) {
                    if (addressItems.street && addressItems.street[0]) {
                        address.street = addressItems.street[0];
                    }
                    if (addressItems.street && addressItems.street[1]) {
                        address.street2 = addressItems.street[1];
                    }
                    if (addressItems.city) {
                        address.city = addressItems.city;
                    }

                    if (addressItems.postcode) {
                        address.postcode = addressItems.postcode;
                    }

                    if (addressItems.regionCode) {
                        address.region = addressItems.regionCode;
                    }

                    if (addressItems.countryId) {
                        address.country_id = addressItems.countryId;
                    }

                    if(address.country_id == 'US' && address.region == 'PR'){
                        address.country_id = 'PR';
                        address.region = '';
                    }

                    start3Action(address);
                } else {
                    console.log("No quote shipping address items");
                    console.log(addressItems);
                }
            } else {
                console.log("No quote shipping address");
                console.log(quote.shippingAddress());
            }
        } else {
            if (document.getElementById("div_preload_access_points7172837www")) {
                document.getElementById("div_preload_access_points7172837www").parentNode.removeChild(document.getElementById("div_preload_access_points7172837www"));
            }
            console.log("Not checked methods");
        }
    }

    function start3Action(address) {
        if (address.country_id && $("#div_upsap_access_points7172837").length == 0) {
            var str = "oId=asdf12432423432423&cburl=" + encodeURIComponent(urlBuilder.build("upsap/index/accesspointcallback") + "?")
                + "&shipAvaIndi=true&target=_self&status=01&loc=" + getLocale() + "&edit=" + rvaPopupApEdit;
            if (rvaPopupApCSS && rvaPopupApCSS.length > 0) {
                str += "&css=" + rvaPopupApCSS;
            }
            if (address.country_id && (address.postcode || (address.region && address.city))) {
                if (address.street) {
                    str += "&add1=" + encodeURIComponent(address.street.substring(0, 64));
                }
                if (address.street2) {
                    str += "&add2=" + encodeURIComponent(address.street2.substring(0, 64));
                }
                if (address.city) {
                    str += "&city=" + encodeURIComponent(address.city);
                }
                if (address.region) {
                    str += "&state=" + encodeURIComponent(address.region);
                }
                if (address.postcode) {
                    str += "&postal=" + encodeURIComponent(address.postcode);
                }
                str += "&country=" + encodeURIComponent(address.country_id);
                var url = "//www.ups.com/lsw/invoke?";
                var urlMy = url + str;
                var zaglushka = document.createElement("DIV");
                zaglushka.setAttribute("id", "div_substrate_access_points7172837");
                zaglushka.style.position = "fixed";
                zaglushka.style.top = "0";
                zaglushka.style.left = "0";
                zaglushka.style.width = getBodyHeight().width + "px";
                zaglushka.style.height = getBodyHeight().height + "px";
                zaglushka.style.opacity = 0.5;
                zaglushka.style.backgroundColor = "#000000";
                zaglushka.style.zIndex = 99990;


                var divchik = document.createElement("DIV");
                divchik.setAttribute("id", "div_upsap_access_points7172837");
                divchik.setAttribute("width", 1080);
                divchik.setAttribute("height", 750);
                if (getBrowserDimensions().width < 1080) {
                    divchik.setAttribute("width", getBrowserDimensions().width * 0.9);
                }
                if (getBrowserDimensions().height < 750) {
                    divchik.setAttribute("height", getBrowserDimensions().height * 0.9);
                }
                divchik.style.position = "fixed";
                divchik.style.top = ((getBrowserDimensions().height / 2) - divchik.getAttribute('height') / 2) + "px";
                divchik.style.left = ((getBrowserDimensions().width / 2) - divchik.getAttribute('width') / 2) + "px";
                divchik.style.backgroundColor = "#ffffff";
                divchik.style.zIndex = 99999;


                var iframe = document.createElement("IFRAME");
                iframe.setAttribute("name", "dialog_upsap_access_points");
                iframe.setAttribute("src", urlMy);
                iframe.setAttribute("width", 1080);
                iframe.setAttribute("height", 750);
                if (getBrowserDimensions().width < 1080) {
                    iframe.setAttribute("width", getBrowserDimensions().width * 0.9);
                }
                if (getBrowserDimensions().height < 750) {
                    iframe.setAttribute("height", getBrowserDimensions().height * 0.9);
                }
                iframe.setAttribute("frameborder", 0);
                /*window.scrollTo(0, 0);*/
                document.body.appendChild(zaglushka);
                document.body.appendChild(divchik);
                document.getElementById("div_upsap_access_points7172837").appendChild(iframe);

            }
        }
    }

    function getAccPointAjaxUrl(o) {
        return "upsap_addLine1=" + encodeURIComponent(o.addLine1)
            + "&upsap_addLine2=" + encodeURIComponent(o.addLine2)
            + "&upsap_addLine3=" + encodeURIComponent(o.addLine3)
            + "&upsap_city=" + encodeURIComponent(o.city)
            + "&upsap_country=" + encodeURIComponent(o.country)
            + "&upsap_fax=" + encodeURIComponent(o.fax)
            + "&upsap_state=" + encodeURIComponent(o.state)
            + "&upsap_postal=" + encodeURIComponent(o.postal)
            + "&upsap_telephone=" + encodeURIComponent(o.telephone)
            + "&upsap_appuId=" + encodeURIComponent(o.appuId)
            + "&upsap_name=" + encodeURIComponent(o.name);
    }

    function setAccessPointAddress(o) {
        if (!o.addLine1) {
            o.addLine1 = '';
        }
        if (!o.addLine2) {
            o.addLine2 = '';
        }
        if (!o.addLine3) {
            o.addLine3 = '';
        }
        var el = $("#onepage-checkout-shipping-method-additional-load");
        if (el && el.length > 0) {
            el.html('<span class="access-point-selected-address">UPS Access Point: ' + o.addLine1 + " " + o.addLine2 + " " + o.addLine3 + ', ' + o.city + ', ' + o.postal + '</span>');
        } else {
            if (document.getElementById("div_preload_access_points7172837www")) {
                document.getElementById("div_preload_access_points7172837www").parentNode.removeChild(document.getElementById("div_preload_access_points7172837www"));
            }
            var divchikPreload = document.createElement("DIV");
            divchikPreload.setAttribute("id", "div_preload_access_points7172837www");
            changeTextRVA(divchikPreload, 'UPS Access Point: ' + o.addLine1 + " " + o.addLine2 + " " + o.addLine3 + ', ' + o.city + ', ' + o.postal);
            el = document.querySelectorAll(".onestepcheckout-shipping-method-block");
            if (el && el.length > 0) {
                el[0].appendChild(divchikPreload);
            }
            else {
                el = document.querySelectorAll(".onestepcheckout-shipping-method-section");
                if (el && el.length > 0) {
                    el[0].appendChild(divchikPreload);
                }
            }
        }
    }

    function closePopapMapRVA(o) {
        removepopapUPSAccessPoint("div_upsap_access_points7172837");
        removepopapUPSAccessPoint("div_substrate_access_points7172837");
        if (!o) {
            quote.shippingMethod(null);
            isSelectedUpsapMethod = false;
            $("#onepage-checkout-shipping-method-additional-load").html("");
        }
    }

    function changeTextRVA(elem, changeVal) {
        if (elem.textContent !== null) {
            elem.textContent = changeVal;
        } else {
            elem.innerText = changeVal;
        }
    }

    function removepopapUPSAccessPoint(id) {
        var element = document.getElementById(id);
        element.parentNode.removeChild(element);
    }

    function getBrowserDimensions() {
        return {
            width: (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth),
            height: (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight)
        };
    }

    function getBodyHeight() {
        var body = document.body,
            html = document.documentElement;

        var height = Math.max(body.scrollHeight, body.offsetHeight,
            html.clientHeight, html.scrollHeight, html.offsetHeight);
        var width = Math.max(body.scrollWidth, body.offsetWidth,
            html.clientWidth, html.scrollWidth, html.offsetWidth);
        return {height: height, width: width};
    }

    function getLocale() {
        var lang = "en_US";
        if (navigator) {
            if (navigator.language) {
                lang = navigator.language;
            }
            else if (navigator.browserLanguage) {
                lang = navigator.browserLanguage;
            }
            else if (navigator.systemLanguage) {
                lang = navigator.systemLanguage;
            }
            else if (navigator.userLanguage) {
                lang = navigator.userLanguage;
            }
        }
        if (lang.indexOf("-") == -1) {
            lang = lang + "_" + lang.toUpperCase();
        }
        /*console.log(lang);*/
        lang = lang.replace("-", "_");
        if ("en_AT,de_AT,nl_BE,fr_BE,en_BE,en_CA,fr_CA,da_DK,en_DK,fr_FR,en_FR,de_DE,en_DE,it_IT,en_IT,es_MX,en_MX,nl_NL,en_NL,pl_PL,en_PL,es_PR,en_PR,es_ES,en_ES,sv_SE,en_SE,de_CH,fr_CH,en_CH,en_GB,en_US".indexOf(lang) != -1) {
            return lang;
        }
        return "en_US";
    }

    window.setAccessPointToCheckout = setAccessPointToCheckout;
    window.closePopapMapRVA = closePopapMapRVA;

    function setAccessPointToCheckout(o) {
        setAccessPointAddress(o);
        $.post(urlBuilder.build("upsap/index/setSessionAddressAP"), getAccPointAjaxUrl(o), function (o2) {
        });
        closePopapMapRVA(o);
    }

    var mixin = {
        selectShippingMethod: function (shippingMethod) {
            if (shippingMethod.carrier_code == 'upsap' && $("#div_upsap_access_points7172837").length == 0) {
                isSelectedUpsapMethod = true;
            }

            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);
            if (shippingMethod.carrier_code == 'upsap' && $("#div_upsap_access_points7172837").length == 0) {
                startAction();
            } else {
                $("#onepage-checkout-shipping-method-additional-load").html("");
            }

            return true;
        },
        isSelected: ko.computed(function () {
                if (quote.shippingMethod() && (isSelectedUpsapMethod === true || quote.shippingMethod().carrier_code != 'upsap')) {
                    if (quote.shippingMethod().carrier_code == 'upsap') {
                        isSelectedUpsapMethod = true;
                    }

                    return quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;
                }

                if (quote.shippingMethod()) {
                    quote.shippingMethod(null);
                    selectShippingMethodAction(null);
                    checkoutData.setSelectedShippingRate('');
                }

                return null;
            }
        )
    };

    return function (target) {
        return target.extend(mixin);
    };
});