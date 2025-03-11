export const HostedBy = ({ host }: { host: string | undefined }) => {
    return <div className={`leading-tighter text-sm`}>Hosted by {host}</div>;
};
