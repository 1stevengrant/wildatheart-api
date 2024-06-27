import { createUseStyles } from "react-jss";

import { getColorAndBackground } from "@/lib/getColorAndBackground";
import { getInitials } from "@/lib/getInitials";

const useStyles = createUseStyles({
    parent: ({ md5email, size }) => ({
        ...getColorAndBackground(md5email),
        position: "relative",
        width: size,
        height: size,
        borderRadius: "50%",
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        boxShadow: "5px 5px 10px rgba(0, 0, 0, 0.15)",
    }),
    swatch: ({ initials, size }) => ({
        // scale the text size depending on avatar size and
        // number of initials
        fontSize: size / (1.4 * Math.max([...initials].length, 2)),
        position: "absolute",
        fontFamily: "sans-serif",
        userSelect: "none",
    }),
    img: ({ size }) => ({
        position: "absolute",
        width: size,
        height: size,
        top: 0,
        left: 0,
        borderRadius: "50%",
    }),
});

export const FallbackAvatar = ({
    name,
    md5email,
    size = 50,
}: {
    name: string;
    md5email: string;
    size?: number;
}) => {
    const url = `https://www.gravatar.com/avatar/${md5email}?s=${String(
        Math.max(50, 250)
    )}&d=blank`;

    const initials = getInitials(`${name}`);

    const c = useStyles({ md5email, size, initials });

    return (
        <div className={c.parent}>
            <div aria-hidden="true" className={c.swatch}>
                {initials}
            </div>
            <img className={c.img} src={String(url)} alt={`${name}’s avatar`} />
        </div>
    );
};
