import { Injectable } from '@angular/core';
import * as QRCode from 'qrcode';
import { Location } from '@angular/common';

@Injectable({
  providedIn: 'root'
})
export class QrcodeService {

  constructor(private location: Location) { }

  async generateQR(data: string): Promise<string> {
    const baseUrl = this.location.prepareExternalUrl('/');
    const fullUrl = `${window.location.origin}${baseUrl}${data}`;
    console.log(fullUrl);
    return QRCode.toDataURL(fullUrl);
  }
}
