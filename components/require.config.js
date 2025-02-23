var components = {
    "packages": [
        {
            "name": "handlebars",
            "main": "handlebars-built.js"
        },
        {
            "name": "jquery",
            "main": "jquery-built.js"
        },
        {
            "name": "jstree",
            "main": "jstree-built.js"
        }
    ],
    "shim": {
        "handlebars": {
            "exports": "Handlebars"
        }
    },
    "baseUrl": "components"
};
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}