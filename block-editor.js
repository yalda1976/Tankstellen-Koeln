// Block Editor Script - Handles Gutenberg block registration and editor functionality

if (typeof wp !== "undefined" && wp.blocks) {
  wp.domReady(() => {
    // Register Gutenberg block
    wp.blocks.registerBlockType("tankstellen/block", {
      title: "Tankstellen Block",
      icon: "location",
      category: "widgets",
      attributes: {
        columns: { type: "number", default: 3 },
        cardColor: { type: "string", default: "#ffffff" },
        textColor: { type: "string", default: "#000000" },
        fontSize: { type: "number", default: 14 },
        padding: { type: "number", default: 10 },
        borderRadius: { type: "number", default: 5 },
      },
      edit: function (props) {
        return wp.element.createElement(
          wp.element.Fragment,
          {},
          wp.element.createElement(
            wp.blockEditor.InspectorControls,
            {},
            wp.element.createElement(
              wp.components.PanelBody,
              { title: "Card Settings" },
              // Card background color picker
              wp.element.createElement(wp.components.ColorPalette, {
                label: "Card Background Color",
                colors: [
                  { name: "White", color: "#ffffff" },
                  { name: "Light Gray", color: "#f8f9fa" },
                  { name: "Dark Gray", color: "#6c757d" },
                  { name: "Black", color: "#000000" },
                ],
                value: props.attributes.cardColor,
                onChange: (value) => props.setAttributes({ cardColor: value }),
              }),
              // Text color picker
              wp.element.createElement(wp.components.ColorPalette, {
                label: "Text Color",
                colors: [
                  { name: "Black", color: "#000000" },
                  { name: "Dark Gray", color: "#343a40" },
                  { name: "Light Gray", color: "#adb5bd" },
                  { name: "White", color: "#ffffff" },
                ],
                value: props.attributes.textColor,
                onChange: (value) => props.setAttributes({ textColor: value }),
              }),
              // Font size control
              wp.element.createElement(wp.components.RangeControl, {
                label: "Font Size",
                value: props.attributes.fontSize,
                onChange: (value) => props.setAttributes({ fontSize: value }),
                min: 10,
                max: 36,
              }),
              // Number of columns control
              wp.element.createElement(wp.components.RangeControl, {
                label: "Number of Columns",
                value: props.attributes.columns,
                onChange: (value) => props.setAttributes({ columns: value }),
                min: 1,
                max: 4,
              }),
              // Border radius control
              wp.element.createElement(wp.components.RangeControl, {
                label: "Border Radius",
                value: props.attributes.borderRadius,
                onChange: (value) =>
                  props.setAttributes({ borderRadius: value }),
                min: 0,
                max: 50,
              }),
              // Padding control
              wp.element.createElement(wp.components.RangeControl, {
                label: "Padding",
                value: props.attributes.padding,
                onChange: (value) => props.setAttributes({ padding: value }),
                min: 0,
                max: 100,
              })
            )
          ),
          // Preview card displayed in the Gutenberg editor
          wp.element.createElement(
            "div",
            {
              className: "card h-100",
              style: {
                backgroundColor: props.attributes.cardColor,
                color: props.attributes.textColor,
                padding: props.attributes.padding + "px",
                borderRadius: props.attributes.borderRadius + "px",
                fontSize: props.attributes.fontSize + "px",
              },
            },
            wp.element.createElement("h5", {}, "Address"),
            wp.element.createElement("p", {}, "Coordinates")
          )
        );
      },
      save: function () {
        return null; // Dynamic block, no content saved
      },
    });
  });
}
