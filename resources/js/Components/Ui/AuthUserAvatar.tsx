import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { UserAvatar } from "@/Components/UserAvatar";

export const AuthUserAvatar = ({ auth }: { auth: any }) => {
    if (!auth.user.image) {
        return (
            <FallbackAvatar
                name={auth.user.full_name}
                md5email={auth.user.md5email}
            />
            // <div>hello</div>
        );
    }
    return (
        <UserAvatar
            className={"h-10 w-10 border-2 border-white"}
            image={auth.user.image}
        />
    );
};
