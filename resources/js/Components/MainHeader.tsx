import MapboxSearch from "@/Components/Maps/MapboxSearch";
import Logo from "@/Components/Logo";

const MainHeader = () => {
    return (
        <header className="p-2 sm:p-4 bg-black text-white flex items-center space-x-2 md:space-x-4">
            <Logo className={'w-[75px] flex-shrink-0 md:w-[150px]'} />
            <MapboxSearch />
        </header>
    )
}

export default MainHeader
