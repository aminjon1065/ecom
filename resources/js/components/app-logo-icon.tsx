import logo from '@/assets/images/logo.png';
import { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(
    props: ImgHTMLAttributes<HTMLImageElement>,
) {
    return <img src={logo} alt="Neutron" {...props} />;
}
