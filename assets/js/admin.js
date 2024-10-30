jQuery(document).ready(function ($) {
  "use strict";

  // Loading Start
  function loading_start($this){
    $this.after('<div class="bulk-content-loader rotating" style="display:block"></div>');
  }

  // Loading Stop
  function loading_stop($this){
    $this.next('.bulk-content-loader').remove();
  }

  // Show Message
  function show_message($this, type, msg){
    $this.after('<div class="message '+type+'">'+msg+'</div>');
    setTimeout(function () {
      $this.next('.message').remove();
    }, 3000);
  }

  // Remove Message
  function remove_message(){
    if($('.message').length > 0){
      $('.message').remove();
    }
  }

  /**
   * Ajax function for global use
   */
  function ajaxCall(data, beforeSendCallback, successCallback, errorCallback, completeCallback) {
    $.ajax({
      url: BULK_MANAGER_ADMIN.ajax_url,
      type: 'POST',
      data: data,
      beforeSend: function () {
        if (beforeSendCallback) {
          beforeSendCallback();
        }
      },
      success: function (response) {
        if (successCallback) {
          successCallback(response);
        }
      },
      error: function (xhr, status, error) {
        if (errorCallback) {
          errorCallback(xhr, status, error);
        }
      },
      complete: function () {
        if (completeCallback) {
          completeCallback();
        }
      },
    });
  }

  var bulk_manager = {
		/**
		 * Initialize bulk manager events.
		 */
		init: function() {
      this.handle_tooltip();
      this.handle_load_gallery_images(BULK_MANAGER_ADMIN.gallery_images);
      this.handle_gallery_upload();
      this.handle_remove_image();
      this.tab_post_product_load_form_data();
      this.tab_taxonomy_load_form_data();
      this.update_post_data();
      this.get_post_data();
      this.update_taxonomy_data();
    },

    /**
     * Load gallery images
     */
    handle_load_gallery_images: function(imageIds, onLoad = true) {

      if (onLoad) {
        imageIds = imageIds?.map((item) => {
          return {
            id: item.id,
            url: item.url,
            alt: "",
            caption: "",
            title: "",
          };
        });
      }
      
      if(imageIds[0]?.url){
        if ($("#bulk-render-gallery-images").length > 0) {
          const galeryTemplate = _.template(
            $("#bulk-render-gallery-images").html()
          );
          const html = galeryTemplate({ items: imageIds });
          $(".bulk-uploaded-gallery-images").html(html);
          bulk_manager.sortGalleryImages();
        }
      }
    },

    /**
     * Sort gallery images
     */
    sortGalleryImages: function() {
      $("#bulk-sortable-list").sortable({
        update: function (event, ui) {
          var sortedItems = $(this).find("li");
          var dataIDs = sortedItems
            .map(function () {
              return $(this).find("span").data("id");
            })
            .get();

          let sortedIds = dataIDs.join(",");
          $(".gallery-image-ids").val(sortedIds);
        },
      });
    },

    /**
     * Update post/taxonomy/term data on submit
     */
    update_taxonomy_data: function(){
      var $selector1 = $('#bulk_manager_selected_types_term_name');
      if($selector1.length > 0){
        $selector1.focusout(function(){
          var value = $(this).val().toLowerCase().replace(/ /g, "-");
          $('#bulk_manager_selected_types_term_slug').val(value);
        });
      }
      var $selector2 = $('#bulk_manager_taxonomies_custom_taxonomy_singular_label');
      if($selector2.length > 0){
        $selector2.focusout(function(){
          var value = $(this).val().toLowerCase().replace(/ /g, "-");
          $('#bulk_manager_taxonomies_custom_taxonomy_slug').val(value);
        });
      }

      $('.bulk-manager-update-taxonomy').on('click', function(e){
        e.preventDefault();
        remove_message();
        var $button = $(this);
        loading_start($button);
        var action_for = $('#bulk_manager_taxonomies_term_action_for').val();
        if('term' == action_for){
          // Action for term
          var action = $('#bulk_manager_taxonomies_term_action').val();
          var payload = bulk_manager.get_payload(action);

          ajaxCall(
            {
              actionType :action,
              payload    :payload,
              security   : BULK_MANAGER_ADMIN.security,
              action     :'taxonomy_terms_update'
            },
            null,
            function(response){
              if(response.success){
                $("#bulk_manager_selected_types_taxonomy_selected").trigger("change");
                
                if('delete_term' != action){
                  setTimeout(function(){
                    $("#bulk_manager_selected_types_taxonomy_terms").val(response.data.term);
                  },500);
                  var nameEl = $('#bulk_manager_selected_types_term_name');
                  var slugEl = $('#bulk_manager_selected_types_term_slug');
                  var descEl = $('#bulk_manager_selected_types_term_description');
                  nameEl.val('');
                  slugEl.val('');
                  descEl.val('');
                }
                
                loading_stop($button);
                show_message($button, 'success',response.data.msg);
              }else{
                loading_stop($button);
                show_message($button, 'error',response.data.msg);
              }
            }
          );
        }else if('taxonomy' == action_for){
          // Action for taxonomy
          var action   = $('#bulk_manager_taxonomies_taxonomy_action').val();
          var postType = $('#bulk_manager_all_post_types_tax').val();
          var postSlug = $('#bulk_manager_taxonomies_custom_taxonomy_slug').val();
          var payload  = bulk_manager.get_payload(action);

          ajaxCall(
            {
              security   : BULK_MANAGER_ADMIN.security,
              action     : 'update_taxonomy',
              actionType : action,
              payload    : payload
            },
            null,
            function(response){
              if(response.success){
                var selectorEl = $("#bulk_manager_selected_types_taxonomy_selected");

                if('delete_taxonomy' == action){
                  bulk_manager.load_taxonomies_by_post_type(postType, selectorEl);
                }else{
                  var afterComplete = function(selectorEl, args){
                    setTimeout(function () {
                      selectorEl.val(args?.slug).change();
                    }, 700);
                  };
                  bulk_manager.load_taxonomies_by_post_type(postType, selectorEl, afterComplete, {slug:response?.data?.slug});
                }

                // Remove values after operation is done
                var slugEl          = $('#bulk_manager_taxonomies_custom_taxonomy_slug');
                var singluarLabelEl = $('#bulk_manager_taxonomies_custom_taxonomy_singular_label');
                var pluralLabelEl   = $('#bulk_manager_taxonomies_custom_taxonomy_plural_label');
                slugEl.val('');
                singluarLabelEl.val('');
                pluralLabelEl.val('');
                
                // Stop loading and Show message
                loading_stop($button);
                show_message($button, 'success',response.data.msg);

              }else{
                loading_stop($button);
                show_message($button, 'error',response.data.msg);
              }
            }
          );
        }else if('update_post' == action_for){
          // update posts by taxonomy/term
          var postType   = $('#bulk_manager_all_post_types_tax').val();
          var posts      = $('#bulk_manager_selected_types_posts_selected').val();
          var action     = $('#bulk_manager_action_type').val();
          var payload    = bulk_manager.get_payload(action);

          if(posts[0] == '0'){
            posts = [];
            $('#bulk_manager_selected_types_posts_selected option').each(function(){
              if($(this).val() != '0'){
                posts.push($(this).val());
              }
            });
          }

          if ((payload === undefined) || (payload == '') || ('none' == posts[0])) {
            loading_stop($button);
            show_message($button, 'success','Payload data not found.');
            return;
          }

          if('taxonomy' == postType) postType = 'product';

          ajaxCall(
            {
              action       : 'update_post_data',
              security     : BULK_MANAGER_ADMIN.security,
              post_type    : postType,
              posts        : posts,
              payload      : payload,
              action_type  : action,
            },
            null,
            function(response){
              if(response.success){
                loading_stop($button);
                show_message($button, 'success',response.data.msg);
              }else{
                loading_stop($button);
                show_message($button, 'error',response.data.msg);
              }
            },
            function (errorThrown) {
              loading_stop($button);
              show_message($button, 'error', 'errorThrown on ajax call');
            },
          );
        }
      });

    },

    /**
     * Retrieve post data
     */
    get_post_data: function () {
      $('#bulk_manager_selected_types_posts_selected').on('change', function() {
        let value = $(this).val();
        if(value.length == 1 && value[0] != 0){
          let type = $('#bulk_manager_action_type').val();
          let taxonomy = null;
          if(type == 'update_taxonomies'){
            taxonomy = $('#bulk_manager_selected_types_update_taxonomies').val();
          }
          ajaxCall(
            {
              action  : "get_post_data",
              security: BULK_MANAGER_ADMIN.security,
              id      : value[0],
              type    : type,
              taxonomy: taxonomy,
            },
            null,
            function(response){
              $('#bulk_manager_selected_types_update_taxonomies_terms').val('');
              $('#bulk_manager_selected_types_update_author').val('');
              $('#bulk-sortable-list').remove();
              if(response.success){
                if(response.data.type == 'content'){
                  tinyMCE.activeEditor.setContent(response.data.output);
                }else if(response.data.type == 'taxonomies'){
                  $('#bulk_manager_selected_types_update_taxonomies_terms').val(response.data.output);
                }else if(response.data.type == 'image'){
                  bulk_manager.handle_load_gallery_images(response.data.output, false);
                }else if(response.data.type == 'gallery_image'){
                  bulk_manager.handle_load_gallery_images(response.data.output, false);
                }else{
                  $('#bulk_manager_selected_types_update_' + response.data.type).val(response.data.output);
                }
              }else{
                console.log('error');
              }
            },
            function (errorThrown) {
              console.log('error');
            },
          );
        }else{
          // tinyMCE.activeEditor.setContent('');
          // $('#bulk_manager_selected_types_update_excerpt').val('');
        }
      });
    },

    /**
     * Handle Tooltip
     */
    handle_tooltip: function () {
      // tooltips
      $(document.body).on("tooltip_init", function () {
        $(".bulk-manager-help-tip").tipTip({
          attribute: "data-tip",
          fadeIn: 50,
          fadeOut: 50,
          delay: 200,
          keepAlive: true,
        });
      });
  
      $(document.body).trigger("tooltip_init");
    },

    /**
     * Gallery uploader
     */
    handle_gallery_upload: function() {
      // on upload button click
      $("body").on("click", "#bulk-browse-button", function (event) {
        event.preventDefault();

        const button = $(this);
        let existingImaegIds = $(
          ".bulk-gallery-uploader #gallery-image-ids"
        ).val();

        const customUploader = wp
          .media({
            title: BULK_MANAGER_ADMIN.insert_btn,
            library: {
              type: "image",
            },
            button: {
              text: BULK_MANAGER_ADMIN.gallery_btn,
            },
            multiple: button.hasClass("multi-image") ? true : false,
          })
          .on("select", function () {
            const attachments = customUploader
              .state()
              .get("selection")
              .map(function (attachment) {
                return attachment.toJSON();
              });

              bulk_manager.handle_load_gallery_images(attachments, false);

            let imageIds = attachments.map(function (attachment) {
              return attachment.id;
            });

            imageIds = imageIds.join(",");
            $(".bulk-gallery-uploader #gallery-image-ids").val(imageIds);
          });

        // already selected images
        customUploader.on("open", function () {
          let ids = existingImaegIds.split(",");
          if (ids.length > 0) {
            const selection = customUploader.state().get("selection");
            ids.forEach((imageId) => {
              const attachment = wp.media.attachment(imageId);
              attachment.fetch();
              selection.add(attachment ? [attachment] : []);
            });
          }
        });

        customUploader.open();
      });
    },

    /**
     * Remove gallery image
     */
    handle_remove_image: function() {
      $(".bulk-uploaded-gallery-images").on("click", "span", function (event) {
        let existingImaegIds = $(
          ".bulk-gallery-uploader #gallery-image-ids"
        ).val();
        let exitstingImageIdsArray = existingImaegIds.split(",");
        let id = $(this).data("id");
        let newData = exitstingImageIdsArray.filter((v) => v != id);
        newData = newData.join(",");
        $(".bulk-gallery-uploader #gallery-image-ids").val(newData);

        $(".bulk-uploaded-gallery-images li")
          .filter(function () {
            return $(this).find("span").data("id") === id;
          })
          .remove();
      });
    },

    /**
     * Load Data onchange/onload 
     */
    tab_post_product_load_form_data: function(){
      $(".show_options_if_checked").each(function () {
        $(this)
          .find("input:eq(0)")
          .on("change", function () {
            if ($(this).is(":checked")) {
              $(this)
                .closest("fieldset, tr")
                .nextUntil(".show_options_if_checked", ".hidden_option")
                .show();
            } else {
              $(this)
                .closest("fieldset, tr")
                .nextUntil(".show_options_if_checked", ".hidden_option")
                .hide();
            }
          })
          .trigger("change");
  
        $(this)
          .find("select:eq(0)")
          .on("change", function () {
            if ($(this).val() != "") {
              $(this)
                .closest("fieldset, tr")
                .nextUntil(".show_options_if_checked", ".hidden_option")
                .show();
            } else {
              $(this)
                .closest("fieldset, tr")
                .nextUntil(".show_options_if_checked", ".hidden_option")
                .hide();
            }
          })
          .trigger("change");
      });
  
      $(".show_match_if_checked").each(function () {
        $(this)
          .find("select:eq(0)")
          .on("change", function () {
            $(this)
              .closest("tr")
              .nextUntil(".show_match_if_checked", ".hidden_option")
              .hide();
  
            var rowClass = ".hidden_option.bulk_manager_selected_types_" + $(this).val();
            $(rowClass).show();
            $("#bulk_manager_selected_types_scheduled_datetime").closest("tr").hide(); // need improvement
            $(".bulk_manager_selected_types_update_status select").val("publish"); // need improvement
          })
          .trigger("change");
      });

      $('.bulk-manager-post-data-type').on('change', function(e){
        let _this = $(this);
        let value = _this.val();

        if(value == 'product'){
          $('#bulk_manager_selected_taxonomies').closest('tr').hide();
          $('#bulk_manager_selected_terms_by_taxonomy').closest('tr').hide();
        }else if(value == 'taxonomy'){
          let selectorTermsEL = $('#bulk_manager_selected_terms_by_taxonomy');
          let taxonomyEl = $('#bulk_manager_selected_taxonomies');
          let taxonomy = taxonomyEl.val();

          let afterComplete = function(selectorTermsEL){
            setTimeout(function () {
              selectorTermsEL.val(selectorTermsEL.val()).change();
            }, 700);
          }
          bulk_manager.load_taxonomy_terms(taxonomy, 'slug', selectorTermsEL, afterComplete);

          taxonomyEl.closest('tr').show();
          selectorTermsEL.closest('tr').show();
        }
      }).trigger("change");

      $("#bulk_manager_selected_taxonomies").on("change", function () {
        $(".bulk-manager-post-data-type").change();
      }).trigger("change");

      $("#bulk_manager_selected_terms_by_taxonomy").on("change", function () {
        var term          = $(this).val();
        var taxonomy       = $("#bulk_manager_selected_taxonomies").val();
        var selectorPostsEl = $("#bulk_manager_selected_types_posts_selected");
        var postType = 'product';
        bulk_manager.load_posts_by_terms(postType, taxonomy, term, selectorPostsEl);
      }).trigger("change");

      $("#bulk_manager_all_post_types")
      .on("change", function () {
        var post_type    = $(this).val();
        var selectorEl = $("#bulk_manager_selected_types_posts_selected");

        bulk_manager.load_posts_by_type(post_type, selectorEl);        
      }).trigger("change");

      $("#bulk_manager_action_type").on("change", function () {
        var value = $(this).val();
        switch (value) {
          case "update_taxonomies":
            $("#bulk_manager_selected_types_update_taxonomies_terms")
              .closest("tr")
              .show();
            var postType = $("#bulk_manager_all_post_types").val();
            if($('#bulk_manager_all_post_types_tax').length > 0){
              postType = $('#bulk_manager_all_post_types_tax').val();
            }

            if('taxonomy' == postType) postType = 'product';
  
            var selectorEl = $("#bulk_manager_selected_types_update_taxonomies");
  
            let afterComplete = function(selectorEl){ 
              var taxonomy = selectorEl.val(),
              selectorTermsEl = $( "#bulk_manager_selected_types_update_taxonomies_terms" );

              bulk_manager.load_taxonomy_terms(taxonomy, 'term_id', selectorTermsEl);
            }
            bulk_manager.load_taxonomies_by_post_type(postType, selectorEl, afterComplete);
            
            break;
  
          case "update_upsells":
          case "update_cross_sells":
            var selectorEl = $('#bulk_manager_selected_types_update_upsells, #bulk_manager_selected_types_update_cross_sells')
            bulk_manager.load_posts_by_type('product', selectorEl); 
            break;
  
          default:
            $('#bulk_manager_selected_types_taxonomy_selected').closest("tr").show();
            $('#bulk_manager_selected_types_taxonomy_terms').closest("tr").show();
            break;
        }
      }).trigger("change");

      $("#bulk_manager_selected_types_update_taxonomies").on("change", function () {
        var taxonomy = $(this).val(),
          selectorTermsEL = $("#bulk_manager_selected_types_update_taxonomies_terms");

          bulk_manager.load_taxonomy_terms(taxonomy, 'term_id', selectorTermsEL);
      });

      $(".bulk_manager_selected_types_update_status select")
      .on("change", function () {
        if ($(this).val() != "future") {
          $(this).closest("tr").next().hide();
        } else {
          $(this).closest("tr").next().show();
        }
      })
      .trigger("change");

    },

    /**
     * Load Data onchange/onload 
     */
    tab_taxonomy_load_form_data: function(){
      $("#bulk_manager_all_post_types_tax")
      .on("change", function () {
        var postType = $(this).val();
        var selectorEl = $("#bulk_manager_selected_types_taxonomy_selected");
        
        let afterComplete = function(selectorEl){ 
          setTimeout(function () {
            selectorEl.trigger("change");
          }, 700);
        }
        bulk_manager.load_taxonomies_by_post_type(postType, selectorEl, afterComplete);

      })
      .trigger("change");

      $("#bulk_manager_selected_types_taxonomy_selected").on("change", function () {
        var taxonomy = $(this).val();
        var selectorTermsEL = $("#bulk_manager_selected_types_taxonomy_terms");

        bulk_manager.load_taxonomy_terms(taxonomy, 'slug', selectorTermsEL);

      });

      $("#bulk_manager_taxonomies_term_action_for")
        .on("change", function () {
          var value = $(this).val();
          if('taxonomy' == value){
            $('#bulk_manager_taxonomies_term_action').closest("tr").hide();
            $('#bulk_manager_selected_types_taxonomy_terms').closest("tr").hide();
            $('#bulk_manager_selected_types_term_name').closest("tr").hide();
            $('#bulk_manager_selected_types_term_slug').closest("tr").hide();
            $('#bulk_manager_selected_types_term_description').closest("tr").hide();
            $('#bulk_manager_taxonomies_custom_taxonomy_slug').closest("tr").show();
            $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').closest("tr").show();
            $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').closest("tr").show();
            $('#bulk_manager_taxonomies_taxonomy_action').closest("tr").show();
            $('#bulk_manager_action_type').closest("tr").hide();
            $('#bulk_manager_selected_types_update_content').closest("tr").hide();
            $('#bulk_manager_selected_types_update_excerpt').closest("tr").hide();
            $('#bulk_manager_selected_types_update_author').closest("tr").hide();
            $('#bulk_manager_selected_types_update_taxonomies').closest("tr").hide();
            $('#bulk_manager_selected_types_update_taxonomies_terms').closest("tr").hide();
            $('#bulk_manager_selected_types_update_status').closest("tr").hide();
            $('#bulk_manager_selected_types_scheduled_datetime').closest("tr").hide();
            $('.bulk_manager_selected_types_update_image').closest("tr").hide();
            $('#bulk_manager_selected_types_posts_selected').closest("tr").hide();
          }else if('term' == value){
            $('#bulk_manager_taxonomies_term_action').closest("tr").show();
            $('#bulk_manager_selected_types_taxonomy_terms').closest("tr").show();
            $('#bulk_manager_selected_types_term_name').closest("tr").show();
            $('#bulk_manager_selected_types_term_slug').closest("tr").show();
            $('#bulk_manager_selected_types_term_description').closest("tr").show();
            $('#bulk_manager_taxonomies_custom_taxonomy_slug').closest("tr").hide();
            $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').closest("tr").hide();
            $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').closest("tr").hide();
            $('#bulk_manager_taxonomies_taxonomy_action').closest("tr").hide();
            $('#bulk_manager_action_type').closest("tr").hide();
            $('#bulk_manager_selected_types_update_content').closest("tr").hide();
            $('#bulk_manager_selected_types_update_excerpt').closest("tr").hide();
            $('#bulk_manager_selected_types_update_author').closest("tr").hide();
            $('#bulk_manager_selected_types_update_taxonomies').closest("tr").hide();
            $('#bulk_manager_selected_types_update_taxonomies_terms').closest("tr").hide();
            $('#bulk_manager_selected_types_update_status').closest("tr").hide();
            $('#bulk_manager_selected_types_scheduled_datetime').closest("tr").hide();
            $('.bulk_manager_selected_types_update_image').closest("tr").hide();
            $('#bulk_manager_selected_types_posts_selected').closest("tr").hide();
          }else{
            $('.bulk-manager-update-taxonomy').prop("disabled",false);
            $('#bulk_manager_taxonomies_term_action').closest("tr").hide();
            $('#bulk_manager_selected_types_term_name').closest("tr").hide();
            $('#bulk_manager_selected_types_term_slug').closest("tr").hide();
            $('#bulk_manager_selected_types_term_description').closest("tr").hide();
            $('#bulk_manager_taxonomies_custom_taxonomy_slug').closest("tr").hide();
            $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').closest("tr").hide();
            $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').closest("tr").hide();
            $('#bulk_manager_taxonomies_taxonomy_action').closest("tr").hide();
            $('#bulk_manager_selected_types_taxonomy_terms').closest("tr").show();
            $('#bulk_manager_action_type').closest("tr").show();
            $('#bulk_manager_action_type').val('update_content');
            $('#bulk_manager_selected_types_update_content').closest("tr").show();
            $('#bulk_manager_selected_types_posts_selected').closest("tr").show();
          }
      })
      .trigger('change');

      $('#bulk_manager_taxonomies_taxonomy_action').on('change', function(){
        $(this).next('.term-delete-notice').remove();
        $('.bulk-manager-update-taxonomy').prop("disabled",false);
        switch ($(this).val()) {
          case 'delete_taxonomy':
              $('#bulk_manager_taxonomies_custom_taxonomy_slug').closest("tr").hide();
              $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').closest("tr").hide();
              $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').closest("tr").hide();

              var taxonomyEl = $('#bulk_manager_selected_types_taxonomy_selected');
              if((taxonomyEl.val() == 'category') || (taxonomyEl.val() == 'post_tag') || (taxonomyEl.val() == 'product_cat') || (taxonomyEl.val() == 'product_tag')){
                $('.bulk-manager-update-taxonomy').prop("disabled",true);
                $(this).after('<div class="term-delete-notice">Default category/post_tag can not be deleted.</div>');
              }
              break;
        
            case 'add_taxonomy':
              $('#bulk_manager_taxonomies_custom_taxonomy_slug').closest("tr").show();
              $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').closest("tr").show();
              $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').closest("tr").show();
              $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').val('');
              $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').val('');
              break;        
        
            case 'edit_taxonomy':
              var taxonomyEl = $('#bulk_manager_selected_types_taxonomy_selected');
              $('#bulk_manager_taxonomies_custom_taxonomy_slug').closest("tr").hide();
              $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').closest("tr").show();
              $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').closest("tr").show();

              if((taxonomyEl.val() == 'category') || (taxonomyEl.val() == 'post_tag') || (taxonomyEl.val() == 'product_cat') || (taxonomyEl.val() == 'product_tag')){
                $('.bulk-manager-update-taxonomy').prop("disabled",true);
                $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').closest("tr").hide();
                $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').closest("tr").hide();
                $(this).after('<div class="term-delete-notice">Default category/post_tag can not be updated.</div>');
                return;
              }

              ajaxCall(
                {
                  taxonomy : taxonomyEl.val(),
                  security : BULK_MANAGER_ADMIN.security,
                  action   :'get_registered_taxonomies'
                },
                null,
                function(response){
                  if(response.success){
                    $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').val(response.data?.taxonomies?.singluarLabel);
                    $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').val(response.data?.taxonomies?.pluralLabel);
                  }
                }
              );
              break;
          
          default:
            break;
        }
      });

      $("#bulk_manager_all_post_types_tax, #bulk_manager_selected_types_taxonomy_selected, #bulk_manager_selected_types_taxonomy_terms")
        .on("change", function () {
          $('.bulk-manager-update-taxonomy').prop("disabled",false);
          if($("#bulk_manager_taxonomies_term_action_for").val() == 'term' ){
            $('#bulk_manager_taxonomies_term_action').val('add_term');
            $('#bulk_manager_selected_types_term_name').val('').closest("tr").show();
            $('#bulk_manager_selected_types_term_slug').val('').closest("tr").show();
            $('#bulk_manager_selected_types_term_description').val('').closest("tr").show();
          }else if($("#bulk_manager_taxonomies_term_action_for").val() == 'taxonomy' ){
            $('.term-delete-notice').remove();
            $('#bulk_manager_taxonomies_taxonomy_action').val('add_taxonomy');
            $('#bulk_manager_taxonomies_custom_taxonomy_singular_label').val('').closest("tr").show();
            $('#bulk_manager_taxonomies_custom_taxonomy_plural_label').val('').closest("tr").show();
            $('#bulk_manager_taxonomies_custom_taxonomy_slug').val('').closest("tr").show();
          }
      });

      setTimeout(function () {
        $("#bulk_manager_selected_types_taxonomy_terms").on("change", function () {
          var term            = $(this).val();
          var postType        = $("#bulk_manager_all_post_types_tax").val();
          var taxonomy        = $("#bulk_manager_selected_types_taxonomy_selected").val();
          var selectorPostsEl = $("#bulk_manager_selected_types_posts_selected");

          bulk_manager.load_posts_by_terms(postType, taxonomy, term, selectorPostsEl);
        }).trigger("change");
      }, 2000);

      $("#bulk_manager_selected_types_taxonomy_selected").on("change", function () {
        setTimeout(function () {
          $("#bulk_manager_selected_types_taxonomy_terms").trigger("change");
        }, 500);
      });

      $("#bulk_manager_taxonomies_term_action")
      .on("change", function () {
        var value = $(this).val();
        switch (value) {
          case "add_term":
            $(".bulk-manager-terms-update-box").val('');
            $(".bulk-manager-terms-update-box").closest("tr").show();
            break;

          case "edit_term":
            $(".bulk-manager-terms-update-box").closest("tr").show();
            
            var taxonomyEl     = $("#bulk_manager_selected_types_taxonomy_selected");
            var editableTermEl = $("#bulk_manager_selected_types_taxonomy_terms");
            var nameEl         = $('#bulk_manager_selected_types_term_name');
            var slugEl         = $('#bulk_manager_selected_types_term_slug');
            var descEl         = $('#bulk_manager_selected_types_term_description');

            ajaxCall(
              {
                taxonomy    : taxonomyEl.val(),
                editableTerm: editableTermEl.val(),
                security    : BULK_MANAGER_ADMIN.security,
                action      :'get_taxonomies_term'
              },
              null,
              function(response){
                if(response.success){
                  nameEl.val(response.data.term.name);
                  slugEl.val(response.data.term.slug);
                  descEl.val(response.data.term.description);
                }
              }
            );
            break;
          case "update_post":
            $(".bulk-manager-terms-update-box").closest("tr").hide();
            break;
          default:
            $(".bulk-manager-terms-update-box").closest("tr").hide();
            break;
        }
      })
      .trigger("change");
    },

    /**
     * Load Data by type 
     */
    load_taxonomies_by_post_type: function(postType, selectorEl, afterComplete = null, args = {}){
      ajaxCall(
        {
          action: "load_taxonomies_by_post_type",
          security: BULK_MANAGER_ADMIN.security,
          postType: postType, 
        },
        null,
        function(response){
          if (response?.success) {
            var object = response.data.taxonimies;
            var output = "";
            for (const property in object) {
              output += `<option value="${property}">${object[property]}</option>`;
            }
            selectorEl.html(output);
            if(afterComplete){
              afterComplete(selectorEl, args);
            }
          }
        },
        function (errorThrown) {
          console.log(errorThrown, "errorThrown on ajax call");
        },
      );
    },

    /**
     * Load posts by type
     */
    load_posts_by_type: function(post_type, selectorEl){
      ajaxCall(
        {
          action: "load_posts_by_type",
          security: BULK_MANAGER_ADMIN.security,
          postType: post_type,     
        },
        null,
        function(response){
          if (response?.success) {
            var object = response.data.postData;
            var output = "";
            for (const property in object) {
              output += `<option value="${property}">${object[property]}</option>`;
            }
            selectorEl.html(output);
          }
        },
        function (errorThrown) {
          console.log(errorThrown, "errorThrown on ajax call");
        },
      );
    },

    /**
     * Load posts by terms
     */
    load_posts_by_terms: function(postType, taxonomy, term, selectorPostsEl){
      ajaxCall(
        {
          action   : "load_posts_by_terms",
          security : BULK_MANAGER_ADMIN.security,
          post_type: postType,
          taxonomy : taxonomy,
          term     : term,
        },
        null,
        function(response){
          if (response?.success) {
            var object = response.data.products;
            
            var output = `<option value="none">None</option>`;
            if(Object.keys(object).length){
              output = `<option value="0">All</option>`;
            }
            for (const property in object) {
              output += `<option value="${property}">${object[property]}</option>`;
            }
            selectorPostsEl.html(output);
          }
        },
        function (errorThrown) {
          console.log(errorThrown, "errorThrown on ajax call");
        },
      );
    },

    /**
     * Load terms by taxonomy
     */
    load_taxonomy_terms: function(taxonomy, type, selectorTermsEL, afterComplete = null){
      ajaxCall(
        {
          action  : "load_taxonomy_terms",
          security: BULK_MANAGER_ADMIN.security,
          taxonomy: taxonomy,
          type    : type,
        },
        null,
        function(response){
          if (response?.success) {
            var object = response.data.terms;
            var output = "";
            for (const property in object) {
              output += `<option value="${property}">${object[property]}</option>`;
            }
            selectorTermsEL.html(output);
            if(afterComplete){
              afterComplete(selectorTermsEL);
            }
          }
        },
        function (errorThrown) {
          console.log(errorThrown, "errorThrown on ajax call");
        },
      );
    },

    /**
     * Update Post Data on submit
     */
    update_post_data: function(){
      $('.bulk-manager-update-post').on('click', function(e){
        e.preventDefault();
        remove_message();
        var $button = $(this);
        loading_start($button);

        var postType   = $('#bulk_manager_all_post_types').val();
        var posts      = $('#bulk_manager_selected_types_posts_selected').val();
        var action     = $('#bulk_manager_action_type').val();
        var payload    = bulk_manager.get_payload(action);

        if(posts[0] == '0'){
          posts = [];
          $('#bulk_manager_selected_types_posts_selected option').each(function(){
            if($(this).val() != '0'){
              posts.push($(this).val());
            }
          });
        }

        if ((payload === undefined) || (payload == '')) {
          loading_stop($button);
          show_message($button, 'success','Payload data not found.');
          return;
        }

        if('taxonomy' == postType) postType = 'product';

        ajaxCall(
          {
            action       : 'update_post_data',
            security     : BULK_MANAGER_ADMIN.security,
            post_type    : postType,
            posts        : posts,
            payload      : payload,
            action_type  : action,
          },
          null,
          function(response){
            if(response.success){
              loading_stop($button);
              show_message($button, 'success',response.data.msg);
            }else{
              loading_stop($button);
              show_message($button, 'error',response.data.msg);
            }
          },
          function (errorThrown) {
            loading_stop($button);
            show_message($button, 'error', 'errorThrown on ajax call');
          },
        );
      });
    },

    /**
     * Retrieve payload data based on action
     */
    get_payload: function (action) {
      let payload;
      switch (action) {
        case 'update_content':
          payload = tinyMCE.activeEditor.getContent();
          break;

        case 'update_image':
          var imageId = 0;
          if($('.bulk-uploaded-gallery-images').length > 0 ){
            imageId = $('.bulk-uploaded-gallery-images #bulk-sortable-list .ui-sortable-handle span').data('id');
          }
          payload = imageId;
          break;

        case 'update_gallery_image':
          var imageIds = [];
          if($('.bulk-uploaded-gallery-images').length > 0 ){
            $('.bulk-uploaded-gallery-images #bulk-sortable-list .ui-sortable-handle').each(function(){
              let id = $(this).find('span').data('id');
              if (id !== undefined && imageIds.indexOf(id) === -1) {
                imageIds.push(id);
              }
            });
          }
          payload = imageIds;
          break;

        case 'update_status':
          var status = null;
          var time = null;
          if($('#bulk_manager_selected_types_update_status').length > 0 ){
            var statusEl = $('#bulk_manager_selected_types_update_status');
            status = statusEl.val();
            if('future' == status){
              time = $('#bulk_manager_selected_types_scheduled_datetime').val();
            }
          }
          payload = [status,time];
          break;

        case 'update_taxonomies':
          var taxonomy = null;
          var terms    = null;
          if($('#bulk_manager_selected_types_update_taxonomies').length > 0 ){
            taxonomy = $('#bulk_manager_selected_types_update_taxonomies').val();
            terms    = $('#bulk_manager_selected_types_update_taxonomies_terms').val();
          }
          payload = [taxonomy,terms];
          break;

        case 'update_delete':
          payload = 'trash'
          break;

        case 'add_taxonomy':
        case 'edit_taxonomy':
          var postEl          = $('#bulk_manager_all_post_types_tax');
          var slugEl          = $('#bulk_manager_taxonomies_custom_taxonomy_slug');
          var singluarLabelEl = $('#bulk_manager_taxonomies_custom_taxonomy_singular_label');
          var pluralLabelEl   = $('#bulk_manager_taxonomies_custom_taxonomy_plural_label');

          if('edit_taxonomy' == action){
            slugEl = $('#bulk_manager_selected_types_taxonomy_selected');
          }

          var post          = postEl.val();
          var slug          = slugEl.val();
          var singluarLabel = singluarLabelEl.val();
          var pluralLabel   = pluralLabelEl.val();
          payload = {post,slug,singluarLabel,pluralLabel};
          break;

        case 'delete_taxonomy':
          var slugEl = $('#bulk_manager_selected_types_taxonomy_selected');
          var slug   = slugEl.val();
          payload = slug;
          break;

        case 'add_term':
        case 'edit_term':
          var taxonomy = $('#bulk_manager_selected_types_taxonomy_selected').val();
          var name     = $('#bulk_manager_selected_types_term_name').val();
          var slug     = $('#bulk_manager_selected_types_term_slug').val();
          var desc     = $('#bulk_manager_selected_types_term_description').val();
          var term = null;
          if('edit_term' == action){
            term = $('#bulk_manager_selected_types_taxonomy_terms').val();
          }
          payload = {taxonomy,slug,name,desc,term};
          break;

        case 'delete_term':
          var taxonomy = $('#bulk_manager_selected_types_taxonomy_selected').val();
          var term     = $('#bulk_manager_selected_types_taxonomy_terms').val();
          payload = {taxonomy,term};
          break;

        default:
          let selector = '#bulk_manager_selected_types_' + action;
          payload = $(selector).val();
          break;
      }
      return payload;
    },
  };

  bulk_manager.init();

});
