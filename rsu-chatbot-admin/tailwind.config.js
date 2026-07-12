import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: "#00685f",
                "primary-light": "#008378",
                background: "#f8f9ff",
                surface: "#ffffff",
                muted: "#e6eeff",
                text: "#0d1c2e",
                success: "#22c55e",
                warning: "#f59e0b",
                danger: "#ef4444",
            },
        },
    },

    plugins: [forms],
};
