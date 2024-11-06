jQuery(document).ready(function($) {
    // customer-card scripts
    activate_customer_card_list_data();
    function activate_customer_card_list_data(){

        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-customer").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            var searchValue = $("#search-customer").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#select-profile, #search-customer").val('');
        });

        $("#new-customer-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_customer_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_customer_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });

        $('[id^="edit-customer-card-"]').on("click", function () {
            const customer_id = this.id.substring(19);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_customer_card_dialog_data',
                    '_customer_id': customer_id,
                },
                success: function (response) {
                    $("#customer-card-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#customer-card-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_customer_card_dialog_data',
                                        '_customer_id': $("#customer-id").val(),
                                        '_customer_code': $("#customer-code").val(),
                                        '_customer_title': $("#customer-title").val(),
                                        '_company_phone': $("#company-phone").val(),
                                        '_company_address': $("#company-address").val(),
                                        '_unified_number': $("#unified-number").val(),
                                    },
                                    success: function (response) {
                                        $("#customer-card-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_customer_card_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this customer?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_customer_card_dialog_data',
                                            '_customer_id': $("#customer-id").val(),
                                        },
                                        success: function (response) {
                                            $("#customer-card-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_customer_card_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#customer-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#customer-card-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // vendor-card scripts
    activate_vendor_card_list_data();
    function activate_vendor_card_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-vendor").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            var searchValue = $("#search-vendor").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#select-profile, #search-vendor").val('');
        });

        $("#new-vendor-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_vendor_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_vendor_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-vendor-card-"]').on("click", function () {
            const vendor_id = this.id.substring(17);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_vendor_card_dialog_data',
                    '_vendor_id': vendor_id,
                },
                success: function (response) {
                    $("#vendor-card-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#vendor-card-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_vendor_card_dialog_data',
                                        '_vendor_id': $("#vendor-id").val(),
                                        '_vendor_code': $("#vendor-code").val(),
                                        '_vendor_title': $("#vendor-title").val(),
                                        '_company_phone': $("#company-phone").val(),
                                        '_company_address': $("#company-address").val(),
                                        '_unified_number': $("#unified-number").val(),
                                    },
                                    success: function (response) {
                                        $("#vendor-card-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_vendor_card_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this vendor?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_vendor_card_dialog_data',
                                            '_vendor_id': $("#vendor-id").val(),
                                        },
                                        success: function (response) {
                                            $("#vendor-card-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_vendor_card_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            }
                        });
                    }
                    $("#vendor-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#vendor-card-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }

    // product-card scripts
    activate_product_card_list_data();
    function activate_product_card_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-product").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            var searchValue = $("#search-product").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#search-product").val('');
        });

        $("#new-product-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_product_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_product_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-product-card-"]').on("click", function () {
            const product_id = this.id.substring(18);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_product_card_dialog_data',
                    '_product_id': product_id,
                },
                success: function (response) {
                    $("#product-card-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#product-card-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_product_card_dialog_data',
                                        '_product_id': $("#product-id").val(),
                                        '_product_code': $("#product-code").val(),
                                        '_product_title': $("#product-title").val(),
                                        '_product_content': $("#product-content").val(),
                                    },
                                    success: function (response) {
                                        $("#product-card-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_product_card_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this product?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_product_card_dialog_data',
                                            '_product_id': $("#product-id").val(),
                                        },
                                        success: function (response) {
                                            $("#product-card-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_product_card_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#product-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#product-card-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // equipment-card scripts
    activate_equipment_card_list_data();
    function activate_equipment_card_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-equipment").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            var searchValue = $("#search-equipment").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#search-equipment").val('');
        });

        $("#new-equipment-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_equipment_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_equipment_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-equipment-card-"]').on("click", function () {
            const equipment_id = this.id.substring(20);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_equipment_card_dialog_data',
                    '_equipment_id': equipment_id,
                },
                success: function (response) {
                    $("#equipment-card-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#equipment-card-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_equipment_card_dialog_data',
                                        '_equipment_id': $("#equipment-id").val(),
                                        '_equipment_code': $("#equipment-code").val(),
                                        '_equipment_title': $("#equipment-title").val(),
                                        '_equipment_content': $("#equipment-content").val(),
                                    },
                                    success: function (response) {
                                        $("#equipment-card-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_equipment_card_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this equipment?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_equipment_card_dialog_data',
                                            '_equipment_id': $("#equipment-id").val(),
                                        },
                                        success: function (response) {
                                            $("#equipment-card-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_equipment_card_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#equipment-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#equipment-card-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // instrument-card scripts
    activate_instrument_card_list_data();
    function activate_instrument_card_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-instrument").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
        
            var searchValue = $("#search-instrument").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#search-instrument").val('');
        });

        $("#new-instrument-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_instrument_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_instrument_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-instrument-card-"]').on("click", function () {
            const instrument_id = this.id.substring(21);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_instrument_card_dialog_data',
                    '_instrument_id': instrument_id,
                },
                success: function (response) {
                    $("#instrument-card-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#instrument-card-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_instrument_card_dialog_data',
                                        '_instrument_id': $("#instrument-id").val(),
                                        '_instrument_code': $("#instrument-code").val(),
                                        '_instrument_title': $("#instrument-title").val(),
                                        '_instrument_content': $("#instrument-content").val(),
                                    },
                                    success: function (response) {
                                        $("#instrument-card-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_instrument_card_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this instrument?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_instrument_card_dialog_data',
                                            '_instrument_id': $("#instrument-id").val(),
                                        },
                                        success: function (response) {
                                            $("#instrument-card-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_instrument_card_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#instrument-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#instrument-card-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // department-card scripts
    activate_department_card_list_data();
    function activate_department_card_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-department").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            var searchValue = $("#search-department").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#search-department").val('');
        });

        $("#new-department-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_department_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_department_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-department-card-"]').on("click", function () {
            const department_id = this.id.substring(21);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_department_card_dialog_data',
                    '_department_id': department_id,
                },
                success: function (response) {
                    $("#department-card-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#department-card-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_department_card_dialog_data',
                                        '_department_id': $("#department-id").val(),
                                        '_department_code': $("#department-code").val(),
                                        '_department_title': $("#department-title").val(),
                                        '_department_content': $("#department-content").val(),
                                    },
                                    success: function (response) {
                                        $("#department-card-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_department_card_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this department?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_department_card_dialog_data',
                                            '_department_id': $("#department-id").val(),
                                        },
                                        success: function (response) {
                                            $("#department-card-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_department_card_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#department-card-dialog").dialog('open');
                    activate_department_user_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#department-card-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // department-user scripts
    function activate_department_user_list_data(){
        $("#new-department-user").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_department_user_list_data',
                },
                success: function (response) {
                    $("#department-user-dialog").html(response.html_contain);
                    $("#department-user-dialog").dialog('open');
                    $('[id^="edit-department-user-"]').on("click", function () {
                        if (window.confirm("Are you sure you want to add this user?")) {
                            const user_id = this.id.substring(21);
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'add_department_user_dialog_data',
                                    '_department_id': $("#department-id").val(),
                                    '_user_id': user_id,
                                },
                                success: function (response) {
                                    $("#department-user-dialog").dialog('close');
                                    $("#department-user-list").html(response.html_contain);
                                    activate_department_user_list_data();
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        }
                    });                        
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
    
        $('[id^="edit-department-user-"]').on("click", function () {
            if (window.confirm("Are you sure you want to delete this user?")) {
                const user_id = this.id.substring(21);
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_department_user_dialog_data',
                        '_department_id': $("#department-id").val(),
                        '_user_id': user_id,
                    },
                    success: function (response) {
                        $("#department-user-list").html(response.html_contain);
                        activate_department_user_list_data();
                    },
                    error: function (error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#department-user-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });    
    }    
});
