<script id="cherrycloud-content-tpl" type="text/x-handlebars-template">
    <div class="header">
        {{#if note.isEditable }}
            <input name="save" type="button" value="Save" class="save" />
        {{else}}
            {{#if note.isRich }}
                <sup>This note has rich text! This version of an app does not support editing of rich text yet.<sup>
            {{else}}
                <sup>This note is readonly! Cannot edit it.<sup>
            {{/if}}
        {{/if}}
        <h2 class="title" id="cherrycloud-node-title">{{ note.title }}</h2>
    </div>
    <div class="input">
        <textarea {{#if note.isEditable }}{{else}}disabled{{/if}}>{{ note.content }}</textarea>
    </div>
</script>
<div id="cherrycloud-editor" class="cherrycloud-editor"></div>