import React from "react";

const FiresMarker = ({
                   className = '',
               }) => {
    return (
        <svg className={className} xmlns="http://www.w3.org/2000/svg" width={31} height={30} fill="none"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.881" d="m6.594 26.337 16.9-7.77M15.65 16.643c4.03-.665 5.441-5.238 3.687-8.65-.92-1.918-2.488-3.264-4.336-4.23.281 2.17-.269 5.844-2.324 5.844-.5.21-.82-2.59-.82-2.952-.243.174-.826.715-1.038.925-3.964 3.777-.445 10.219 4.832 9.063Z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.881" d="M16.531 11.844s-.618 1.452-2.367 1.184M10.668 20.44l-4.074-1.872m16.9 7.77-4.837-2.225"/></svg>

    )
}

export default FiresMarker
