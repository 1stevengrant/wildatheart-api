import { UserAvatar } from "@/Components/UserAvatar";

export const AuthUserAvatar = ({ auth }: { auth: any }) => {
    return <UserAvatar className={"h-10 w-10"} image={auth.user.image} />;
};
