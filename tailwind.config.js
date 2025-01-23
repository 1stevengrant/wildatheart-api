/*
 * NOTE: this file has holdovers from the previous design system.
 * Moving forward, we should do our best to align with tailwind and the design system.
 */

/**
 * Colors
 * https://www.figma.com/design/kgPud8tPmNVObrB936pZf0/WAH-Design-System?node-id=55-991&node-type=frame&t=rOR0RZcqBn8VQ4PN-0
 */
const colors = {
    // Default
    black: "#010101",
    white: "#ffffff",
    body: "#010101",

    // Primary
    p50: "#FBEFE6",
    p75: "#EEBD96",
    p100: "#E7A26B",
    p200: "#DD7A2B",
    p300: "#D65F00",
    p400: "#964300",
    p500: "#833A00",

    // Secondary
    s50: "#EEF3F4",
    s75: "#B8CDD1",
    s100: "#9BB8BE",
    s200: "#6F9AA2",
    s300: "#52858F",
    s400: "#395D64",
    s500: "#325157",

    // Neutrals
    n0: "#FFFFFF",
    n10: "#FAFAFB",
    n20: "#F5F6F6",
    n30: "#ECEDED",
    n40: "#E0E1E2",
    n50: "#C3C6C8",
    n60: "#B5B9BA",
    n70: "#A9ADAF",
    n80: "#9AA0A2",
    n90: "#8C9295",
    n100: "#7D8488",
    n200: "#6F777A",
    n300: "#61696D",
    n400: "#555E62",
    n500: "#465055",
    n600: "#3A454A",
    n700: "#29353A",
    n800: "#1B272D",
    n900: "#0F1C22",

    // Success
    g50: "#F2F4EE",
    g75: "#CBD0BB",
    g100: "#B6BD9F",
    g200: "#96A075",
    g250: "#008000",
    g300: "#818D59",
    g400: "#5A633E",
    g500: "#4F5636",

    // Danger
    r50: "#FBE6E6",
    r75: "#ED9999",
    r100: "#E56F6F",
    r200: "#DA3131",
    r300: "#D20707",
    r400: "#930505",
    r500: "#800404",

    // Warning
    y50: "#FBF8E6",
    y75: "#EEE299",
    y100: "#E7D66F",
    y200: "#DCC531",
    y300: "#D5B907",
    y400: "#958205",
    y500: "#827104",

    // From Nav Mockups
    // https://www.figma.com/design/exRUpLNLT1Vxq2kN3QB8dc/WAH-Web-Mocks?node-id=716-640&node-type=vector&m=dev
    /**
     * We likely need to have a chat with Wookie to decide how
     * we want to add these colors to the official color system.
     */
    "matt-grey": "#202020",
    "matt-black": "#0D151A",

    // Defaults from Tailwind we want
    transparent: "transparent",
    current: "currentColor",
};

/**
 * Font Sizes
 * https://www.figma.com/design/kgPud8tPmNVObrB936pZf0/WAH-Design-System?node-id=24-316&node-type=canvas&t=rOR0RZcqBn8VQ4PN-0
 */
const fontSize = {
    hero1: "clamp(2.25rem, 1.6848rem + 2.4117cqw, 4rem)",
    hero2: "clamp(1.75rem, 1.6693rem + 0.3445vw, 2rem)",
    h1: "clamp(1.75rem, 3.5cqw, 3rem)",
    h2: "clamp(1.25rem, 3.5cqw, 1.5rem)",
    h3: "clamp(1.1rem, 3.5cqw, 1.375rem)",
    h4: "clamp(.875rem, 3.5cqw, 1.25rem)",
    h5: "clamp(.75rem, 3.5cqw, 1.125rem)",
    h6: ".875rem",
    "body-extra-small": ".625rem",
    "body-small": ".75rem",
    "body-base": "1rem",
    "body-large": "clamp(1rem, 3.5cqw, 1.4375rem)",
};

/** @type {import("tailwindcss").Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.{tsx,ts,vue,js,jsx}",
    ],
    theme: {
        colors,
        fontSize,
        extend: {
            animation: {
                zoom: "zoom 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)",
                fade: "fade 0.2s ease-out",
            },
            backgroundImage: {
                campfire: "url('/resources/img/fires-image.webp')",
            },
            borderRadius: {
                "3xl": "1.875rem",
            },
            borderWidth: {
                half: ".5px",
            },
            container: {
                center: true,
                padding: "1.5rem",
                paddingLeft: "1.5rem",
                paddingRight: "1.5rem",
                maxWidth: "1373px",
            },
            fontFamily: {
                sans: ["astoria-sans", "sans-serif"],
            },
            fontSize: {
                xxs: ".625rem", // 10px
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
            keyframes: {
                zoom: {
                    "0%": {
                        transform: "scale(0.95)",
                    },
                    "100%": {
                        transform: "scale(1)",
                    },
                },
                fade: {
                    "0%": {
                        opacity: "0",
                    },
                    "100%": {
                        opacity: "1",
                    },
                },
            },
            letterSpacing: {
                "even-wider": "0.07em",
                widest: "0.2em",
            },
            scale: {
                300: "3",
            },
            screens: {
                "ipad-landscape": "1180px",
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        p: {
                            fontSize: theme("fontSize.md"),
                        },
                        li: {
                            fontSize: theme("fontSize.md"),
                        },
                        h1: {
                            marginBottom: ".5rem",
                        },
                        h2: {
                            marginTop: "1rem",
                            marginBottom: ".5rem",
                        },
                    },
                },
            }),
        },
    },
    plugins: [
        require("@tailwindcss/container-queries"),
        require("@tailwindcss/typography"),
        require("flowbite/plugin"),
        require("@headlessui/tailwindcss"),
    ],
};
