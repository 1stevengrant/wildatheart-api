/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./vendor/wire-elements/pro/config/wire-elements-pro.php",
        "./vendor/wire-elements/pro/**/*.blade.php",
    ],
    theme: {
        fontWeight: {
            hairline: 100,
            thin: 200,
            light: 300,
            normal: 400,
            medium: 500,
            semibold: 600,
            bold: 700,
            extrabold: 800,
            black: 900,
        },
        colors: {
            "transparent-orange": "rgba(214, 95, 0, 0.9)",
            "transparent-brown": "rgba(116, 54, 23, 0.9)",
            black: "#0f1c22", //From Sketch File
            light: "#f2f3f3",
            error: "red",
            dark: "#0f1c22",
            darker: "#081013",
            "grey-darkest": "#1d272b",
            "grey-darker": "#606f7b",
            "grey-dark": "#5C6771", // From Sketch File
            grey: "#9FA7A9",
            "grey-light": "var(--wah-grey-light)", //From Sketch File
            "grey-lighter": "#f1f5f8",
            "grey-lightest": "#f8fafc",
            white: "#ffffff",
            "red-darkest": "#3b0d0c",
            "red-darker": "#621b18",
            "red-dark": "#cc1f1a",
            red: "#e3342f",
            "red-light": "#ef5753",
            "red-lighter": "#f9acaa",
            "red-lightest": "#fcebea",

            orange: "#D65F00",
            "blue-darkest": "#06272E",
            "blue-darker": "#185f68", // From Borders in styleguide
            blue: "#52858F", // From style guide
            brown: "#743617",
        },
        fontFamily: {
            cursive: ["Rock Salt", "cursive"],
            sans: [
                "Nunito Sans",
                "system-ui",
                "BlinkMacSystemFont",
                "-apple-system",
                "Helvetica Neue",
                "sans-serif",
            ],
            serif: ["Arno Pro", "arno-pro", "Georgia", "serif"],
            mono: ["Menlo", "monospace"],
        },
        fontSize: {
            xs: ".75rem", // 12px
            sm: ".875rem", // 14px
            base: "1rem", // 16px
            md: "1.125rem", //18px
            lg: "1.25rem", // 20px
            xl: "1.5rem", // 24px
            "2xl": "1.625rem", // 26px
            "3xl": "1.875rem", // 30px
            "4xl": "2.125rem", // 34px
            "5xl": "3.875rem", // 48px
        },

        lineHeight: {
            tighter: 1.17,
            none: 1,
            tight: 1.25,
            normal: 1.5,
            loose: 2,
            looser: 2.5,
            loosest: 3,
        },
        letterSpacing: {
            tight: "-0.05em",
            normal: "0",
            wide: "0.03em",
            wider: "0.06em",
            widest: "0.07em",
        },
        extend: {},
    },
    plugins: [require("@tailwindcss/forms")],
};
