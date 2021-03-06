{{#if note }}
    <div class="header">
        {{#if note.isEditable }}
            <input name="save" type="button" value="Save" class="save" />
        {{else}}
            <sup>
            {{#if note.isRich }}
                This note has rich text! This version of an app does not support editing of rich text yet.
                {{else}}
                This note is readonly! Cannot edit it.
                {{/if}}
            </sup>
        {{/if}}
        <h2 class="title" id="note-node-title">{{ note.title }}</h2>
    </div>
    <div class="input">
        <textarea {{#if note.isEditable }}{{else}}disabled{{/if}}>{{ note.content }}</textarea>
    </div>
{{else}}
    <div class="header">
        No any note activated
    </div>
{{/if}}
