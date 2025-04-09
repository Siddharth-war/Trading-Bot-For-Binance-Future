<select onchange="change_mail_route(this.value)" class="custom-select w-auto min-width-170px">
    <option value="user" {{ Request::is('admin/business-settings/email-setup/user*') ? 'selected' : '' }}>{{ translate('Customer_Mail_Templates') }}</option>
    <option value="dm" {{ Request::is('admin/business-settings/email-setup/dm*') ? 'selected' : '' }}>{{ translate('Deliveryman_Mail_Templates') }}</option>
</select>
