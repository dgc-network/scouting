jQuery(document).ready(function($) {
    // embedded
    const prevEmbeddedId = $("#prev-embedded-id").val();
    const nextEmbeddedId = $("#next-embedded-id").val();

    // Function to navigate to the previous or next record
    function navigateToEmbedded(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_embedded_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextEmbeddedId) {
            navigateToEmbedded(nextEmbeddedId); // Move to the next record
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevEmbeddedId) {
            navigateToEmbedded(prevEmbeddedId); // Move to the previous record
        }
    });

    // Touch navigation for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    $(document).on("touchstart", function (event) {
        touchStartX = event.originalEvent.changedTouches[0].screenX;
    });

    $(document).on("touchend", function (event) {
        touchEndX = event.originalEvent.changedTouches[0].screenX;
        handleEmbeddedSwipe();
    });

    function handleEmbeddedSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextEmbeddedId) {
            navigateToEmbedded(nextEmbeddedId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevEmbeddedId) {
            navigateToEmbedded(prevEmbeddedId); // Swipe right: Move to the previous record
        }
    }

    // itinerary
    activate_itinerary_list_data();
    function activate_itinerary_list_data(){
        $("#new-itinerary").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_itinerary_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_itinerary_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-itinerary-"]').on("click", function () {
            const itinerary_id = this.id.substring(15);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_itinerary_dialog_data',
                    '_itinerary_id': itinerary_id,
                },
                success: function (response) {
                    if (response.html_contain === "") {
                        //alert("Itinerary not found.");
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set("_itinerary_title", response.title);
                        window.location.href = currentUrl.toString();
                        //return;
                    } else {
                        $("#itinerary-dialog").html(response.html_contain);
                        //if ($("#is-site-admin").val() === "1") {
                            $("#itinerary-dialog").dialog("option", "buttons", {
                                "Save": function () {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'set_itinerary_dialog_data',
                                            '_itinerary_id': $("#itinerary-id").val(),
                                            '_itinerary_title': $("#itinerary-title").val(),
                                            '_itinerary_content': $("#itinerary-content").val(),
                                            '_itinerary_url': $("#itinerary-url").val(),
                                            '_itinerary_category': $("#itinerary-category").val(),
                                        },
                                        success: function (response) {
                                            $("#itinerary-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_itinerary_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                },
                                "Delete": function () {
                                    if (window.confirm("Are you sure you want to delete this itinerary?")) {
                                        $.ajax({
                                            type: 'POST',
                                            url: ajax_object.ajax_url,
                                            dataType: "json",
                                            data: {
                                                'action': 'del_itinerary_dialog_data',
                                                '_itinerary_id': $("#itinerary-id").val(),
                                            },
                                            success: function (response) {
                                                $("#itinerary-dialog").dialog('close');
                                                $("#result-container").html(response.html_contain);
                                                activate_itinerary_list_data();
                                            },
                                            error: function (error) {
                                                console.error(error);
                                                alert(error);
                                            }
                                        });
                                    }
                                },
                            });
                            $("#itinerary-dialog").dialog('open');
                            $("#itinerary-preview").on("click", function() {
                                const currentUrl = new URL(window.location.href);
                                currentUrl.searchParams.set("_itinerary_title", $("#itinerary-title").val());
                                window.location.href = currentUrl.toString();
                            });
            
                    }
                    //}
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#itinerary-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }
});
