import {Link, InertiaLinkProps} from '@inertiajs/react';

export default function FooterNavLink({
                                          active = false,
                                          className = '',
                                          children,
                                          ...props
                                      }: InertiaLinkProps & {
    active: boolean
}) {
    return (
        <Link
            {...props}
            className={
                '' +
                (active
                    ? ' text-orange'
                    : '') +
                className
            }
        >
            {children}
        </Link>
    );
}
