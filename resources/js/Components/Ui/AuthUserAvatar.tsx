import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { UserAvatar } from "@/Components/UserAvatar";

export const AuthUserAvatar = ({ auth }: { auth: any }) => {
    if (!auth.user.image) {
        return <FallbackAvatar user={auth.user} />;
    }
    return (
        <UserAvatar
            className={"h-10 w-10 border-2 border-white"}
            image={auth.user.image}
        />
    );
};
