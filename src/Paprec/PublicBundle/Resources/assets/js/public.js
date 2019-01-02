$(function () {
    'use strict';

    /*******************************************************************************************************************
     * COMMON
     */

    // Si il y a la div step__agency alors on charge son contenu
    if ($('.step__agency').is('div')) {
        reloadNearbyAgencies()
    }

    // Si l'input "locationSelect" existe, alors on lui initialise un autocomplete Google et on force l'input de nombre
    if ($('#locationSelect').is('input')) {
        $('#locationSelect').keypress(function (key) {
            if (key.charCode < 48 || key.charCode > 57) {
                return false;
            }
        });

        if ($('.nonCorporate-form').is('div')) {
            initializeAutocomplete('locationSelect', true);
        } else {
            initializeAutocomplete('locationSelect');
        }
    }

    // Si l'input "divisionSelect" existe, alors on intercepte les modifications de division
    // Quand on sélectionne une division dans le select, on recharge en envoyant la location et la division
    if ($('#divisionSelect').is('select')) {
        $('#divisionSelect').on('change', function () {
            var url = $('#divisionSelect').data('url').replace('divisionTmp', $('#divisionSelect').val());
            $(location).attr('href', url);
        });
    }

    // Si l'input "frequencyRadioBox" existe, alors on intercepte les modifications des boutons radio
    // Quand on sélectionne une frequence dans les radioButtons, on recharge la page en envoyant la location, division, frequence
    // cela na nous faire naviguer vers le SubscriptionController de la division choisie
    if ($('#frequencyRadioBox').is('div')) {
        $('#frequencyRadioBox').change(function () {
            var frequency = $("input[name='frequencyRadio']:checked").val();
            var url = $(this).data('url').replace('frequencyTmp', frequency);
            $(location).attr('href', url);
        });
    }

    if ($('#divisionType').is('input') || $('#divisionSelect').is('select')) {
        colorBodyFromDivision();
    }

    /*******************************************************************************************************************
     * REGULAR FORM
     */
    if ($('.regular-form').is('div')) {

        // Au submit du formulaire, on rajoute les attributs au <form> avant de POST
        $('#regularFormSubmitButton').on('click', function () {
            var form = $('form');
            form.attr('action', '#');
            form.attr('method', 'post');
            form.attr('enctype', 'multipart/form-data');
            form.submit();
        });

        // // Affichage des fichiers sélectionnés en PJ
        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     var html = "";
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        //
        // });

    }

    /*******************************************************************************************************************
     * CONTACT FORM
     */
    if ($('.contact-form').is('div')) {
        console.log('test');
        $('#contactFormSubmitButton').on('click', function () {
            $('#contactForm').submit();
        });

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     var html = "";
        //     for (var i = 0; i <  $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').files.length; i++) {
        //         var lastModified = new Date( $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').files[i].lastModified);
        //         html +=  $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        // });
    }

    /*******************************************************************************************************************
     * CONTACT FROM CART FORM
     */
    if ($('.contact-from-cart-form').is('div')) {
        $('#contactFormSubmitButton').on('click', function () {
            $('#contactForm').submit();
        });

        reloadCart(true);

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     html = "";
        //
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        //
        // });
    }

    /*******************************************************************************************************************
     * CALLBACK FORM
     */
    if ($('.callBack-form').is('div')) {

        reloadCart(true);

        /**
         * Gestion des datepickers
         */
            // On ne peut choisir une date de rappel qu'à partir d'aujourd'hui
        var now = new Date();

        // On définit arbitrairement la date maximum pour le rappel à dans 3 mois
        var maxDate = moment(now);
        maxDate.add(3, 'months');

        $('#paprec_commercialbundle_callBack_dateCallBack').datetimepicker({
            locale: 'fr',
            format: 'L',
            minDate: now,
            maxDate: maxDate,
            icons:
                {
                    up: 'fa fa-angle-up',
                    down: 'fa fa-angle-down',
                    date: 'fa fa-calendar',
                    time: 'fa fa-clock',
                    next: 'fa fa-angle-right',
                    previous: 'fa fa-angle-left'
                },
        });

        $('#paprec_commercialbundle_callBack_timeCallBack').datetimepicker({
            locale: 'fr',
            format: 'LT',
            icons:
                {
                    up: 'fa fa-angle-up',
                    down: 'fa fa-angle-down',
                },
        });


        $('#callBackFormSubmitButton').on('click', function () {
            // avant de submit, on convertit la date au format yyyy-mm-dd
            $('#paprec_commercialbundle_callBack_dateCallBack').val($('#paprec_commercialbundle_callBack_dateCallBack').val().split('/').reverse().join('-'));
            $('#callBackForm').submit();
        });
    }

    /*******************************************************************************************************************
     * COLLECTIVITE FORM
     */
    if ($('.collectivite-form').is('div')) {
        $('#groupFormSubmitButton').on('click', function () {
            $('#regularForm').submit();
        });

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     var html = "";
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"javascript:void(0);\" data-name=\"" + this.files[i].name + "\" class=\"removeFileButton\">x</a><br>";
        //         $('#listFiles').html(html);
        //         var that = this;
        //     }
        //
        //     $('.removeFileButton').on('click', function () {
        //         var idx;
        //         var files = Array.from(that.files);
        //         var found = files.some(function (item, index) { f = index; return item.name === $(this).data('name')});
        //         files.splice(idx, 1);
        //         console.dir(files);
        //     });
        // });
    }

    /*******************************************************************************************************************
     * GROUPE & RESEAUX FORM
     */
    if ($('.groupe-reseau-form').is('div')) {

        $('#groupFormSubmitButton').on('click', function () {
            $('#regularForm').submit();
        });

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     html = "";
        //
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        //
        // });
    }

    /*******************************************************************************************************************
     * PARTICULIER FORM
     */
    if ($('.particulier-form').is('div')) {
        $('#groupFormSubmitButton').on('click', function () {
            $('#regularForm').submit();
        });

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     html = "";
        //
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        //
        // });
    }

    /*******************************************************************************************************************
     * NEED FORM
     */
    if ($('.need-form').is('div')) {
        reloadCart();

        // Au clic sur un produit, on l'ajoute ou on le supprime des displayedCategories
        $('.productCheckboxPicto').click(function () {
            var url = $(this).data('url');
            $(location).attr('href', url);
        });

        // Au clic sur la croix de l'infoproduct, on supprime le produit des displayedProduct
        $('.infoproduct__close').click(function () {
            var url = $(this).data('url');
            $(location).attr('href', url);
        });
    }

    /*******************************************************************************************************************
     * DI OU CHANTIER NEED FORM
     */
    if ($('.di-need-form').is('div') || $('.chantier-need-form').is('div')) {
        // Au clic sur une catégorie, on l'ajoute où on la supprime des displayedCategories du cart
        $('.categoryCheckboxPicto').click(function () {
            var url = $(this).data('url');
            $(location).attr('href', url);
        });

        // Au clic sur un wrapper, on l'affiche ou on le cache
        $('.step__wrappertop').click(function () {
            if ($('.step__wrappertop').hasClass('open')) {
                $('.step__wrappertop').removeClass('open')
            } else {
                $('.step__wrappertop').addClass('open');
            }
        });

        /**
         * On intercepte le clic sur le bouton "Ajouter au panier" pour récupérer le produit et la catégorie et l'ajouter au cart
         */
        $('.addToCartSubmitButton').click(function () {
            var url = $(this).data('url');

            var productCategory = (this.id).replace('addToCartSubmitButton_', '').split('_', 2);
            var productId = productCategory[0];
            var categoryId = productCategory[1];
            var qtty = $('#quantityProducSelect_' + productId + '_' + categoryId).val();
            url = url.replace('quantityTmp', qtty);
            $.ajax({
                type: "POST",
                url: url,
                success: function (response) {
                    // Quand on ajoute un produit au devis, on referme l'affichage des infos du produit ajouté
                    removeBadge(productId, categoryId);
                    $('#productCheckboxPicto_' + productId + '_' + categoryId).prepend("<span class=\"number\">" + qtty + "<span");
                    reloadCart()
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        });
    }

    /*******************************************************************************************************************
     * D3E NEED FORM
     */
    if ($('.d3e-need-form').is('div')) {

        $('.addToCartSubmitButton').click(function () {
            var url = $(this).data('url');

            var productId = (this.id).replace('addToCartSubmitButton_', '');
            var qtty = $('#quantityProducSelect_' + productId).val();
            var optHandling = $('#optHandlingProductSelect_' + productId).prop('checked') ? 1 : 0;
            var optSerialNumberStmt = $('#optSerialNumberStmtProductSelect_' + productId).prop('checked') ? 1 : 0;
            var optDestruction = $('#optDestructionProductSelect_' + productId).prop('checked') ? 1 : 0;
            url = url
                .replace('quantityTmp', qtty)
                .replace('optHandlingTmp', optHandling)
                .replace('optSerialNumberStmtTmp', optSerialNumberStmt)
                .replace('optDestructionTmp', optDestruction);
            $.ajax({
                type: "POST",
                url: url,
                success: function (response) {
                    // Quand on ajoute un produit au devis, on referme l'affichage des infos du produit ajouté
                    removeBadge(productId);
                    $('#productCheckboxPicto_' + productId).prepend("<span class=\"number\">" + qtty + "<span");
                    reloadCart()
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        });
    }

    /*******************************************************************************************************************
     * CONTACT DETAILS FORM
     */
    if ($('.contact-details-form').is('div')) {
        reloadCart();

        $('#contactDetailsFormSubmitButton').on('click', function () {
            $('#contactDetailsForm').submit();
        });
    }

    /*******************************************************************************************************************
     * CHANTIER ET D3E DELIVERY FORM
     */
    if ($('.delivery-form').is('div')) {
        reloadCart(true);

        /**
         * Gestion des datepickers
         */
        var now = new Date();
        var minDate = moment(now);
        minDate.add(2, "days");
        // On définit arbitrairement la date maximum pour le rappel à dans 3 mois
        maxDate = moment(now);
        maxDate.add(3, 'months');
        /**
         * CHANTIER
         */
        if ($('.chantier-delivery-form').is('div')) {
            $('#paprec_commercialbundle_productchantierorderdelivery_installationDate').datetimepicker({
                locale: 'fr',
                format: 'L',
                minDate: minDate,
                maxDate: maxDate,
                icons:
                    {
                        up: 'fa fa-angle-up',
                        down: 'fa fa-angle-down',
                        date: 'fa fa-calendar',
                        time: 'fa fa-clock',
                        next: 'fa fa-angle-right',
                        previous: 'fa fa-angle-left'
                    },
            });

            $('#paprec_commercialbundle_productchantierorderdelivery_removalDate').datetimepicker({
                locale: 'fr',
                format: 'L',
                minDate: minDate,
                maxDate: maxDate,
                icons:
                    {
                        up: 'fa fa-angle-up',
                        down: 'fa fa-angle-down',
                        date: 'fa fa-calendar',
                        time: 'fa fa-clock',
                        next: 'fa fa-angle-right',
                        previous: 'fa fa-angle-left'
                    },
            });

            $('#deliveryFormSubmitButton').on('click', function () {
                // avant de submit, on convertit la date au format yyyy-mm-dd
                $('#paprec_commercialbundle_productchantierorderdelivery_installationDate').val($('#paprec_commercialbundle_productchantierorderdelivery_installationDate').val().split('/').reverse().join('-'));
                $('#paprec_commercialbundle_productchantierorderdelivery_removalDate').val($('#paprec_commercialbundle_productchantierorderdelivery_removalDate').val().split('/').reverse().join('-'));

                $('#deliveryForm').submit();
            });
        }
        /**
         * D3E
         */
        if ($('.d3e-delivery-form').is('div')) {
            $('#paprec_commercialbundle_productd3eorderdelivery_installationDate').datetimepicker({
                locale: 'fr',
                format: 'L',
                minDate: minDate,
                maxDate: maxDate,
                icons:
                    {
                        up: 'fa fa-angle-up',
                        down: 'fa fa-angle-down',
                        date: 'fa fa-calendar',
                        time: 'fa fa-clock',
                        next: 'fa fa-angle-right',
                        previous: 'fa fa-angle-left'
                    },
            });

            $('#paprec_commercialbundle_productd3eorderdelivery_removalDate').datetimepicker({
                locale: 'fr',
                format: 'L',
                minDate: minDate,
                maxDate: maxDate,
                icons:
                    {
                        up: 'fa fa-angle-up',
                        down: 'fa fa-angle-down',
                        date: 'fa fa-calendar',
                        time: 'fa fa-clock',
                        next: 'fa fa-angle-right',
                        previous: 'fa fa-angle-left'
                    },
            });


            $('#deliveryFormSubmitButton').on('click', function () {
                $('#paprec_commercialbundle_productd3eorderdelivery_installationDate').val($('#paprec_commercialbundle_productd3eorderdelivery_installationDate').val().split('/').reverse().join('-'));
                $('#paprec_commercialbundle_productd3eorderdelivery_removalDate').val($('#paprec_commercialbundle_productd3eorderdelivery_removalDate').val().split('/').reverse().join('-'));
                $('#deliveryForm').submit();
            });
        }
    }

    /*******************************************************************************************************************
     *PAYMENT FORM
     */
    if ($('.payment-form').is('div')) {
        reloadCart(true);
    }

});

/****************************************************************
 * FUNCTIONS
 ***************************************************************/

/**
 * Récupération du nombre d'agences proches
 */
function reloadNearbyAgencies() {
    if ($('.step__agency').is('div')) {
        var url = $('.step__agency').data('url');
        $.ajax({
            type: "GET",
            url: url,
            contentType: "html",
            success: function (response) {
                // On récupère l'HTML des agences proches et on l'insère dans step__agency dans la sidebar
                var htmlToDisplay = response.trim();
                $(".step__agency").html(htmlToDisplay);
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    }
}

/**
 * Rechargement de l'HTML du Cart
 */
function reloadCart(readonly) {
    var url = $('#loadedCartPanel').data('url');
    var division = $('#loadedCartPanel').data('division');
    $.ajax({
        type: "GET",
        url: url,
        contentType: "html",
        success: function (response) {
            // On récupère l'HTML du cart dans "Mon besoin" et on l'insère dans loadedCartPanel dans la sidebar
            var htmlToDisplay = response.trim();
            $("#loadedCartPanel").html(htmlToDisplay);
            if (readonly) {
                //on supprime les croix dans le panier
                $('.buttonDeleteProduct').remove();
            } else {
                // On ajoute un listener sur les "x" dans la liste des produits dans le Cart
                $(".buttonDeleteProduct").click(function () {
                    var urlRemove = $(this).data('url');
                    if (division === 'D3E') {
                        productId = (this.id).replace('buttonDeleteProduct_', '');
                        $.ajax({
                            type: "GET",
                            dataType: "json",
                            url: urlRemove.replace('productTmp', productId),
                            success: function (response) {
                                removeBadgeD3E(productId);
                                reloadCart();
                            },
                            error: function (errorThrown) {
                                console.log(errorThrown);
                            }
                        });
                    } else {
                        productCategory = (this.id).replace('buttonDeleteProduct_', '').split('_', 2);
                        productId = productCategory[0];
                        categoryId = productCategory[1];
                        $.ajax({
                            type: "GET",
                            dataType: "json",
                            url: urlRemove
                                .replace('categoryTmp', categoryId)
                                .replace('productTmp', productId),
                            success: function (response) {
                                removeBadge(productId, categoryId);
                                reloadCart();
                            },
                            error: function (errorThrown) {
                                console.log(errorThrown);
                            }
                        });
                    }
                })
            }
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

/**
 * Supprime le badge au dessus d'un produit indiquant la quantité de ce produit ajoutée au panier
 * @param productId
 * @param categoryId
 */
function removeBadge(productId, categoryId) {
    $('#productCheckboxPicto_' + productId + '_' + categoryId).find('span.number').remove();
}

/**
 * POUR D3E
 * Supprime le badge au dessus d'un produit indiquant la quantité de ce produit ajoutée au panier
 * @param productId
 * @param categoryId
 */
function removeBadgeD3E(productId) {
    $('#productCheckboxPicto_' + productId).find('span.number').remove();
}

/**
 * Initialise l'autocomplete Google sur l'input ayant pour id = "id"
 * si nonCorporate = true, on souhaite récupérer uniquement le codePostal de l'autocomplete Google
 */
function initializeAutocomplete(id, nonCorporate = false) {
    var element = document.getElementById(id);
    var options = {
        types: ['(regions)'],
        componentRestrictions: {country: "fr"}
    };
    if (element) {
        var autocomplete = new google.maps.places.Autocomplete(element, options);
        if (nonCorporate) {
            google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChangedNonCorporate);
        } else {
            google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChanged);
        }
    }
}

/**
 * Fonction appelée lorsque l'on choisit une proposition de l'autocomplete Google
 */
function onPlaceChanged() {
    var loc = $('#locationSelect').val();
    var place = this.getPlace();
    // On init les variables car si un champ manque, on se retrouve avec city/%20/long dans l'url et non city//long
    var city = " ";
    var postalCode = " ";

    var lat = place.geometry.location.lat();
    var long = place.geometry.location.lng();
    for (var i in place.address_components) {
        var component = place.address_components[i];
        for (var j in component.types) {  // Some types are ["country", "political"]
            if (component.types[j] === 'postal_code') {
                postalCode = component.long_name;
            }
            if (component.types[j] === 'locality') {
                city = component.long_name;
            }
        }
    }
    var url = $('#locationSelect').data('url')
        .replace('locationTmp', loc)
        .replace('cityTmp', city)
        .replace('postalCodeTmp', postalCode)
        .replace('longTmp', long)
        .replace('latTmp', lat);
    $(location).attr('href', url);
}


/**
 * Fonction appelée lorsque l'on choisit une proposition de l'autocomplete Google pour un formulaire nonCorporate (Groupe & Réseaux, Collectivité, Particulier)
 */
function onPlaceChangedNonCorporate() {
    var place = this.getPlace()
    for (var i in place.address_components) {
        var component = place.address_components[i];
        for (var j in component.types) {  // Some types are ["country", "political"]
            if (component.types[j] === 'postal_code') {
                $('#paprec_commercialbundle_quoteRequestNonCorporate_postalCode').val(component.long_name);
            }
        }
    }
}

/**
 * On récupère la valeur du champ divisionSelect et on applique au body la couleur de la division choisie
 */
function colorBodyFromDivision() {
    var division = '';
    if ($('#divisionSelect').is('select')) {
        division = $('#divisionSelect').val();
    } else if ($('#divisionType').is('input')) {
        division = $("#divisionType").val();
    }
    if (division !== '') {
        if (division === 'DI') {
            $('body').addClass('tunnel--green');
        } else if (division === 'CHANTIER') {
            $('body').addClass('tunnel--orange');
        } else if (division === 'D3E') {
            $('body').addClass('tunnel--purple');
        }
    }
}




