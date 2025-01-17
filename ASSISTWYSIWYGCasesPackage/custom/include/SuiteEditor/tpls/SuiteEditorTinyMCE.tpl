{**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 *}

<!-- [TinyMCE Editor implementation] -->

<script src="{$assist_tiny_cdn_info.url}" integrity="{$assist_tiny_cdn_info.integrity}" crossorigin="anonymous"></script>
<script>

    /**
     * TinyMCE value getter function
     *
     * @returns string - tinymce value
     */
    SuiteEditor.getValue = function() {ldelim}
        return tinymce.get('{$elementId}').getContent();
    {rdelim};

    /**
     * TinyMCE value setter function
     *
     * @param htmlCode
     */
    SuiteEditor.apply = function(htmlCode) {ldelim}
        if(typeof htmlCode === 'undefined') {ldelim}
            htmlCode = '';
        {rdelim}
        tinyMCE.EditorManager.get('{$elementId}').setContent(htmlCode);
    {rdelim};

    /**
     * Mozaik value insert function
     *
     * @param text
     * @param elemId
     */
    SuiteEditor.insert = function(text, elemId) {ldelim}
        if(typeof elemId === 'undefined') {ldelim}
            elemId = '{$elementId}';
        {rdelim}
        if(elemId != '{$elementId}') {ldelim}
            throw 'incorrect editor element id (TinyMCE id: '+elemId+')';
        {rdelim}

        tinyMCE.activeEditor.execCommand('mceInsertRawHTML', false, text);

    {rdelim};

    $(function(){ldelim}

        {if $clickHandler}
        $('#{$elementId}').click({$clickHandler});
        {/if}

        $(window).mouseup(function(){ldelim}
            $('#{$textareaId}').val(SuiteEditor.getValue());
        {rdelim});
    {rdelim});
</script>
<script>
    tinyMCE.baseURL = "{$assist_tiny_cdn_info.base_url}";
    tinyMCE.suffix = '.min';
    tinymce.init(
        $.extend({$tinyMCESetup},
            {ldelim} selector:'#{$elementId}',
                menubar: false,
                default_link_target: '_blank',
                branding: false,
                browser_spellcheck: true,
                plugins: [
                    'advlist autolink lists link image imagetools charmap hr print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: ['undo redo | formatselect | fontselect |  fontsizeselect | ',
                    'bold italic forecolor backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | image | link |help |' +
                    'hr table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter'],
                file_picker_types: 'image',
                automatic_uploads: true,
                fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
                images_upload_url: 'index.php?entryPoint=tinymceimagepost',
                convert_urls: false,
            {rdelim},
        ));</script>

<div id="{$elementId}" name="{$elementId}" title="">{$contents}</div>