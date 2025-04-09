<div class="modal fade" id="instructions">
    <div class="modal-dialog status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="single-item-slider owl-carousel">
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/1.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b>{{translate('Select_Theme')}}</b></h5>
                                <p>
                                    {{ translate('Choose_a_related_email_template_theme_for_the_purpose_for_which_you_are_creating_the_email.')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/5.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"><b>{{translate('Choose_Logo')}}</b></h5>
                                <p>
                                    {{translate('Upload_your_company_logo_in_1:1_format._This_will_show_above_the_Main_Title_of_the_email.')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/2.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b> {{translate('Write_a_Title')}} </b> </h5>
                                <p>
                                    {{translate('Give_your_email_a_‘Catchy_Title’_to_help_the_reader_understand_easily.')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/3.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b> {{translate('Write_a_message_in_the_Email_Body')}} </b> </h5>
                            </div>
                            <p>
                                {{ translate('you_can_add_your_message_using_placeholders_to_include_dynamic_content._Here_are_some_examples_of_placeholders_you_can_use:') }}
                            </p>
                            <ul>
                                <li>
                                    {userName}: {{ translate('the_name_of_the_user.') }}
                                </li>
                                <li>
                                    {deliveryManName}: {{ translate('the_name_of_the_delivery_person.') }}
                                </li>
                                <li>
                                    {restaurantName}: {{ translate('the_name_of_the_restaurant.') }}
                                </li>
                                <li>
                                    {orderId}: {{ translate('the_order_id.') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/4.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b> {{translate('Add_Button_&_Link')}} </b> </h5>
                                <p>
                                    {{translate('Specify_the_text_and_URL_for_the_button_that_you_want_to_include_in_your_email.')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/5.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b> {{translate('Change_Banner_Image_if_needed')}} </b> </h5>
                                <p>
                                    {{translate('Choose_the_relevant_banner_image_for_the_email_theme_you_use_for_this_mail.')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/6.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b> {{translate('Add_Content_to_Email_Footer')}} </b> </h5>
                                <p>
                                    {{translate('Write_text_on_the_footer_section_of_the_email,_and_choose_important_page_links_and_social_media_links.')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/7.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b> {{translate('Create_a_copyright_notice')}} </b> </h5>
                                <p>
                                    {{translate('Include_a_copyright_notice_at_the_bottom_of_your_email_to_protect_your_content.')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/admin/img/email-templates/8.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title"> <b> {{translate('Save_and_publish')}} </b> </h5>
                                <p>
                                    {{translate("Once_you've_set_up_all_the_elements_of_your_email_template,_save_and_publish_it_for_use.")}}
                                </p>
                                <button class="btn btn-primary w-100 mw-300px" data-dismiss="modal" type="button">{{translate('Got_It')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <div class="slide-counter"></div>
                </div>
            </div>
        </div>
    </div>
</div>
