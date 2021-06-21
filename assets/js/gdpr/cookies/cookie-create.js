/**
 *  Cookies create
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (name, value) {
    let secure = location.protocol !== "http:"
    let domainName = document.domain
    let domain = domainName.replace('www.', '')
    Cookies.set(name, value, {expires: 365, path: '/', domain: domain, secure: secure})
}