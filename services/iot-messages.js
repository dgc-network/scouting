jQuery(document).ready(function($) {
    // iot-message scripts
    activate_iot_message_list_data();
    function activate_iot_message_list_data(){

        $("#select-todo").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var todoValue = $("#select-todo").val();
            if (todoValue) {
                queryParams.push("_select_todo=" + todoValue);
            }

            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });
    }

});
