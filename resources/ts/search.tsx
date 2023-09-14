import React from 'react';
import {createRoot} from 'react-dom/client';
import {RecoilRoot} from "recoil";
import SearchApp from "./SearchApp";
// @ts-ignore
const root = createRoot(document.getElementById('root'));
root.render(
    <RecoilRoot>
        <SearchApp/>
    </RecoilRoot>
);
