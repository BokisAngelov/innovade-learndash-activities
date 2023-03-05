jQuery(document).ready(function ($) {

    // onclickcomplete ajax trigger
    $('body').on('click', '.on-click-complete', function () {
        $topic_id = $(this).attr("data-id");

        $.ajax(
            {
            type: "get",
            data: {
                action: 'innovade_complete_topic',
                topic_id: $topic_id
            },
            dataType: "json",
            url: my_ajax_object.ajax_url,
            complete: function (msg) {
                location.reload(true);
            }
        });
    });

});
